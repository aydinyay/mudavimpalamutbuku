<?php
declare(strict_types=1);

namespace Mudavim\Core;

use RuntimeException;

/**
 * Fiziksel Sayım Yöneticisi
 *
 * Be Truly kararları:
 * - Kör sayım: UI'ya theoretical_qty sayım SONRASI gönderilir (anchoring bias önlemi)
 * - WAC korunur: düzeltmelerde avg_cost değişmez, sadece net_qty override edilir
 * - Eşik: 500₺+ fark → pending_approval, müdür/patron onayı gerekir
 * - Draft: yarım kalan sayım sunucuda saklanır, devam edilebilir
 * - Öncelik: last_purchase_price × net_qty = "depoda yatan para" — en pahalısı önce
 */
class CountManager
{
    private \PDO $pdo;

    /** Otomatik onay eşiği — bu değerin üstündeki fark pending_approval'a düşer */
    private const AUTO_APPROVE_THRESHOLD = 500.0;

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    // =========================================================
    // BUGÜN SAYILACAK ÜRÜNLER
    // =========================================================

    /**
     * Bugün sayılması gereken ürünleri öncelik sırasıyla döner.
     * Öncelik = last_purchase_price × mevcut_stok (depodaki para değeri)
     * + count_priority_override = 1 olanlar daima başa alınır
     * + count_frequency'ye göre bugün sayılması gerekenler filtrelenir
     */
    public function getTodayCountables(int $locationId, int $limit = 30): array
    {
        $dayOfWeek = (int) date('N'); // 1=Pzt ... 7=Paz
        $dayOfMonth = (int) date('j');

        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.name,
                p.count_frequency,
                p.count_priority_override,
                p.last_purchase_price,
                p.min_stock_qty,
                u.abbreviation      AS unit,
                u.id                AS unit_id,
                COALESCE(sb.net_qty, 0) AS theoretical_qty,
                -- Öncelik skoru: depodaki para değeri
                (p.last_purchase_price * COALESCE(sb.net_qty, 0)) AS stock_value,
                c.product_type
            FROM products p
            JOIN units u        ON u.id  = p.stock_unit_id
            JOIN categories c   ON c.id  = p.category_id
            LEFT JOIN stock_balances sb
                ON sb.product_id = p.id AND sb.location_id = :lid
            WHERE
                p.is_tracked   = 1
                AND p.deleted_at IS NULL
                AND c.product_type != 'fixed_asset'
                AND p.count_frequency != 'never'
                AND (
                    p.count_priority_override = 1
                    OR p.count_frequency = 'daily'
                    OR (p.count_frequency = 'weekly'  AND :dow  = 1)  -- Pazartesi
                    OR (p.count_frequency = 'monthly' AND :dom = 1)   -- Ay başı
                )
            ORDER BY
                p.count_priority_override DESC,
                (p.last_purchase_price * COALESCE(sb.net_qty, 0)) DESC,
                p.name ASC
            LIMIT :lmt
        ");
        $stmt->bindValue(':lid', $locationId, \PDO::PARAM_INT);
        $stmt->bindValue(':dow', $dayOfWeek,  \PDO::PARAM_INT);
        $stmt->bindValue(':dom', $dayOfMonth, \PDO::PARAM_INT);
        $stmt->bindValue(':lmt', $limit,      \PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        // Be Truly: theoretical_qty UI'ya gönderilmez burada;
        // sadece draft kaydında saklanır, client summary aşamasında ister.
        // Şimdilik full veriyi döndürüyoruz — API katmanı filtreleyecek.
        return $products;
    }

    // =========================================================
    // DRAFT YÖNETİMİ
    // =========================================================

    /**
     * Bugün bu lokasyon için açık draft varsa döner, yoksa yeni oluşturur.
     */
    public function getOrCreateDraft(int $locationId, int $userId): array
    {
        $existing = $this->getExistingDraft($locationId);
        if ($existing) return $existing;

        $this->pdo->prepare("
            INSERT INTO stock_counts (count_date, location_id, status, created_by)
            VALUES (CURDATE(), ?, 'in_progress', ?)
        ")->execute([$locationId, $userId]);

        $countId  = (int) $this->pdo->lastInsertId();
        $products = $this->getTodayCountables($locationId);

        // Draft items oluştur (counted_qty = -1 → henüz sayılmamış sentinel)
        $insertItem = $this->pdo->prepare("
            INSERT IGNORE INTO stock_count_items
                (count_id, product_id, location_id, theoretical_qty, counted_qty, unit_cost)
            VALUES (?, ?, ?, ?, -1, ?)
        ");
        foreach ($products as $p) {
            $insertItem->execute([
                $countId,
                $p['id'],
                $locationId,
                $p['theoretical_qty'],
                $p['last_purchase_price'],
            ]);
        }

        return $this->buildDraftResponse($countId, $locationId, $products);
    }

    public function getExistingDraft(int $locationId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT sc.*, COUNT(sci.id) AS total_items,
                   SUM(sci.counted_qty >= 0) AS counted_items
            FROM stock_counts sc
            LEFT JOIN stock_count_items sci ON sci.count_id = sc.id
            WHERE sc.location_id = ?
              AND sc.count_date  = CURDATE()
              AND sc.status      = 'in_progress'
            GROUP BY sc.id
            ORDER BY sc.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$locationId]);
        $draft = $stmt->fetch();
        if (!$draft) return null;

        $products = $this->getCountItems((int) $draft['id'], $locationId);
        return $this->buildDraftResponse((int) $draft['id'], $locationId, $products, $draft);
    }

    private function buildDraftResponse(int $countId, int $locationId, array $products, array $meta = []): array
    {
        $counted = array_filter($products, fn($p) => ($p['counted_qty'] ?? -1) >= 0);
        return [
            'count_id'      => $countId,
            'status'        => $meta['status'] ?? 'in_progress',
            'count_date'    => $meta['count_date'] ?? date('Y-m-d'),
            'products'      => $products,
            'progress'      => [
                'counted' => count($counted),
                'total'   => count($products),
            ],
        ];
    }

    private function getCountItems(int $countId, int $locationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT sci.*, p.name, p.last_purchase_price,
                   u.abbreviation AS unit, u.id AS unit_id,
                   COALESCE(sb.net_qty, sci.theoretical_qty) AS current_theoretical
            FROM stock_count_items sci
            JOIN products p  ON p.id  = sci.product_id
            JOIN units    u  ON u.id  = p.stock_unit_id
            LEFT JOIN stock_balances sb ON sb.product_id = sci.product_id AND sb.location_id = ?
            WHERE sci.count_id = ?
            ORDER BY sci.id ASC
        ");
        $stmt->execute([$locationId, $countId]);
        return $stmt->fetchAll();
    }

    // =========================================================
    // SAYIM KALEMİ KAYDET (anlık, her ürün sonrası)
    // =========================================================

    /**
     * Tek bir ürünün sayım sonucunu kaydeder.
     * Draft modunda — stok henüz değişmez.
     */
    public function saveCountItem(int $countId, int $productId, float $countedQty, string $reason = 'unknown'): array
    {
        $allowed = ['normal_waste', 'theft_suspected', 'entry_error', 'natural_shrinkage', 'unknown'];
        if (!in_array($reason, $allowed)) $reason = 'unknown';

        // Teorik stoğu al
        $stmt = $this->pdo->prepare("
            SELECT sci.theoretical_qty, sci.unit_cost, p.name, u.abbreviation AS unit
            FROM stock_count_items sci
            JOIN products p ON p.id = sci.product_id
            JOIN units u ON u.id = p.stock_unit_id
            WHERE sci.count_id = ? AND sci.product_id = ?
        ");
        $stmt->execute([$countId, $productId]);
        $item = $stmt->fetch();
        if (!$item) throw new RuntimeException('Sayım kalemi bulunamadı.');

        $variance = round($countedQty - (float) $item['theoretical_qty'], 4);

        $this->pdo->prepare("
            UPDATE stock_count_items
            SET counted_qty     = ?,
                variance_reason = ?
            WHERE count_id = ? AND product_id = ?
        ")->execute([$countedQty, $reason, $countId, $productId]);

        return [
            'product_id'      => $productId,
            'product_name'    => $item['name'],
            'unit'            => $item['unit'],
            'theoretical_qty' => (float) $item['theoretical_qty'],
            'counted_qty'     => $countedQty,
            'variance'        => $variance,
            'variance_value'  => round(abs($variance) * (float) $item['unit_cost'], 2),
        ];
    }

    // =========================================================
    // SAYIMI ONAYLA VE STOĞA İŞLE
    // =========================================================

    /**
     * Sayımı onaylar, stok düzeltmelerini uygular.
     *
     * Be Truly: WAC korunur — net_qty direkt override, avg_cost değişmez.
     * Be Truly: 500₺+ fark → pending_approval.
     */
    public function confirmCount(int $countId, int $userId, string $userRole = 'manager'): array
    {
        return Database::transaction(function (\PDO $pdo) use ($countId, $userId, $userRole) {

            $count = $pdo->prepare("SELECT * FROM stock_counts WHERE id = ? AND status IN ('in_progress','pending_approval')")->execute([$countId]);
            // Re-fetch properly
            $cStmt = $pdo->prepare("SELECT * FROM stock_counts WHERE id = ? AND status IN ('in_progress','pending_approval')");
            $cStmt->execute([$countId]);
            $count = $cStmt->fetch();
            if (!$count) throw new RuntimeException('Geçerli bir sayım bulunamadı.');

            $locationId = (int) $count['location_id'];

            // Sayılan kalemleri al (sadece counted_qty >= 0)
            $itemsStmt = $pdo->prepare("
                SELECT sci.*, p.name, u.abbreviation AS unit
                FROM stock_count_items sci
                JOIN products p ON p.id = sci.product_id
                JOIN units    u ON u.id  = p.stock_unit_id
                WHERE sci.count_id = ? AND sci.counted_qty >= 0
            ");
            $itemsStmt->execute([$countId]);
            $items = $itemsStmt->fetchAll();

            if (empty($items)) throw new RuntimeException('Hiç sayım kalemi girilmemiş.');

            $totalVarianceValue = 0;
            $adjustments        = [];
            $today              = date('Y-m-d');

            foreach ($items as $item) {
                $theoretical = (float) $item['theoretical_qty'];
                $counted     = (float) $item['counted_qty'];
                $variance    = round($counted - $theoretical, 4);
                $unitCost    = (float) $item['unit_cost'];
                $varValue    = abs($variance) * $unitCost;
                $totalVarianceValue += $varValue;

                if (abs($variance) < 0.001) {
                    // Fark yok, sadece theoretical_qty'yi güncelle (sayım tarihi referansı)
                    $adjustments[] = [
                        'product_id'  => $item['product_id'],
                        'name'        => $item['name'],
                        'variance'    => 0,
                        'value'       => 0,
                        'type'        => 'none',
                    ];
                    continue;
                }

                if ($variance < 0) {
                    // Fiziksel < Teorik → Stok açığı → outlet
                    $outletTypeId = $this->getOutletTypeId('COUNT_ADJUSTMENT_DN');
                    $pdo->prepare("
                        INSERT INTO stock_outlets
                            (outlet_type_id, product_id, location_id, quantity, unit_id,
                             unit_cost, outlet_date, reference_note, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ")->execute([
                        $outletTypeId,
                        $item['product_id'],
                        $locationId,
                        abs($variance),
                        $item['unit_id'] ?? 1,
                        $unitCost,
                        $today,
                        "Sayım #{$countId} — {$item['variance_reason']}",
                        $userId,
                    ]);
                    $adjType = 'shortage';
                } else {
                    // Fiziksel > Teorik → Fazla → entry (WAC korunacak — unit_price=0)
                    $entryTypeId = $this->getEntryTypeId('COUNT_ADJUSTMENT_UP');
                    $pdo->prepare("
                        INSERT INTO stock_entries
                            (entry_type_id, product_id, location_id, quantity_gross,
                             quantity_net, unit_id, unit_price, entry_date,
                             reference_note, created_by)
                        VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?)
                    ")->execute([
                        $entryTypeId,
                        $item['product_id'],
                        $locationId,
                        $variance,
                        $variance,
                        $item['unit_id'] ?? 1,
                        $today,
                        "Sayım #{$countId} fazla düzeltme",
                        $userId,
                    ]);
                    $adjType = 'surplus';
                }

                // Be Truly: net_qty direkt override — WAC değişmez
                $pdo->prepare("
                    INSERT INTO stock_balances (product_id, location_id, net_qty, gross_qty, avg_cost)
                    VALUES (:pid, :lid, :qty, :qty, :cost)
                    ON DUPLICATE KEY UPDATE
                        net_qty   = :qty2,
                        gross_qty = :qty3
                        -- avg_cost kasıtlı olarak güncellenmez (WAC korunması)
                ")->execute([
                    ':pid'   => $item['product_id'],
                    ':lid'   => $locationId,
                    ':qty'   => $counted,
                    ':cost'  => $unitCost,
                    ':qty2'  => $counted,
                    ':qty3'  => $counted,
                ]);

                // Sayım kalemine teorik miktarı kaydet (referans için)
                $pdo->prepare("UPDATE stock_count_items SET theoretical_qty = ? WHERE count_id=? AND product_id=?")
                    ->execute([$theoretical, $countId, $item['product_id']]);

                $adjustments[] = [
                    'product_id'    => $item['product_id'],
                    'name'          => $item['name'],
                    'unit'          => $item['unit'],
                    'theoretical'   => $theoretical,
                    'counted'       => $counted,
                    'variance'      => $variance,
                    'value'         => round($varValue, 2),
                    'type'          => $adjType,
                    'reason'        => $item['variance_reason'],
                ];

                AuditManager::log('stock_count_items', (int)$item['id'], 'UPDATE', null, [
                    'count_id'  => $countId,
                    'product'   => $item['name'],
                    'variance'  => $variance,
                ]);
            }

            // Onay eşiği kontrolü
            $autoApprove  = $totalVarianceValue <= self::AUTO_APPROVE_THRESHOLD;
            $highRoleAuto = in_array($userRole, ['patron', 'manager']);
            $finalStatus  = ($autoApprove || $highRoleAuto) ? 'confirmed' : 'pending_approval';

            if ($finalStatus === 'confirmed') {
                $pdo->prepare("UPDATE stock_counts SET status='confirmed', confirmed_by=?, confirmed_at=NOW() WHERE id=?")
                    ->execute([$userId, $countId]);
            } else {
                $pdo->prepare("UPDATE stock_counts SET status='pending_approval' WHERE id=?")
                    ->execute([$countId]);
            }

            return [
                'count_id'            => $countId,
                'status'              => $finalStatus,
                'total_variance_value' => round($totalVarianceValue, 2),
                'needs_approval'      => $finalStatus === 'pending_approval',
                'adjustments'         => $adjustments,
            ];
        });
    }

    // =========================================================
    // YARDIMCI
    // =========================================================

    private function getOutletTypeId(string $code): int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM outlet_types WHERE code = ?");
        $stmt->execute([$code]);
        $id = $stmt->fetchColumn();
        if (!$id) throw new RuntimeException("Outlet tipi bulunamadı: {$code}");
        return (int) $id;
    }

    private function getEntryTypeId(string $code): int
    {
        $stmt = $this->pdo->prepare("SELECT id FROM entry_types WHERE code = ?");
        $stmt->execute([$code]);
        $id = $stmt->fetchColumn();
        if (!$id) throw new RuntimeException("Entry tipi bulunamadı: {$code}");
        return (int) $id;
    }
}
