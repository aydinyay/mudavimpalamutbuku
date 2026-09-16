<?php
declare(strict_types=1);

namespace Mudavim\Core;

/**
 * Demirbaş Envanter Yöneticisi
 *
 * Çatal/bıçak/tabak/minder/runner ve benzeri sabit varlıklar.
 * Yemek stoğundan farkı: WAC yok, reçete yok, velocity yok.
 * Sadece: kaç tane aldık — kaç kırıldı — kaç kaldı — nerede.
 */
class AssetManager
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    // ── Tüm demirbaşları getir (lokasyona göre toplamlı) ───

    public function getAll(int $locationId = 0): array
    {
        $locFilter = $locationId > 0 ? 'AND sb.location_id = :lid' : '';

        $stmt = $this->pdo->prepare("
            SELECT
                p.id,
                p.name,
                p.sku,
                COALESCE(SUM(sb.net_qty), 0)                   AS total_qty,
                COALESCE(broken.broken_qty, 0)                 AS broken_this_month,
                COALESCE(lost.lost_qty,   0)                   AS lost_this_month,
                COALESCE(SUM(sb.net_qty), 0) - COALESCE(broken.broken_qty,0) - COALESCE(lost.lost_qty,0)
                                                               AS serviceable_qty,
                p.count_frequency
            FROM products p
            JOIN categories c ON c.id = p.category_id AND c.product_type = 'fixed_asset'
            LEFT JOIN stock_balances sb ON sb.product_id = p.id {$locFilter}
            -- Bu ay kırılanlar
            LEFT JOIN (
                SELECT so.product_id, SUM(so.quantity) AS broken_qty
                FROM stock_outlets so
                JOIN outlet_types ot ON ot.id = so.outlet_type_id AND ot.code = 'BREAKAGE'
                WHERE so.outlet_date >= DATE_FORMAT(CURDATE(),'%Y-%m-01')
                  AND so.deleted_at IS NULL
                GROUP BY so.product_id
            ) broken ON broken.product_id = p.id
            -- Bu ay kayıp
            LEFT JOIN (
                SELECT so.product_id, SUM(so.quantity) AS lost_qty
                FROM stock_outlets so
                JOIN outlet_types ot ON ot.id = so.outlet_type_id AND ot.code = 'ASSET_LOST'
                WHERE so.outlet_date >= DATE_FORMAT(CURDATE(),'%Y-%m-01')
                  AND so.deleted_at IS NULL
                GROUP BY so.product_id
            ) lost ON lost.product_id = p.id
            WHERE p.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.name
        ");

        $params = [];
        if ($locationId > 0) $params[':lid'] = $locationId;
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Kırık / Kayıp kaydet ───────────────────────────────

    /**
     * @param string $outletTypeCode BREAKAGE | ASSET_LOST
     */
    public function recordLoss(
        int    $productId,
        float  $qty,
        string $outletTypeCode,
        int    $locationId,
        int    $createdBy,
        string $note = ''
    ): int {
        return Database::transaction(function (\PDO $pdo) use (
            $productId, $qty, $outletTypeCode, $locationId, $createdBy, $note
        ) {
            // Outlet type id
            $otStmt = $pdo->prepare("SELECT id FROM outlet_types WHERE code=?");
            $otStmt->execute([$outletTypeCode]);
            $otId = $otStmt->fetchColumn();
            if (!$otId) throw new \RuntimeException("Outlet tipi bulunamadı: {$outletTypeCode}");

            // Birim id (adet)
            $unitStmt = $pdo->prepare("SELECT id FROM units WHERE abbreviation='adet' LIMIT 1");
            $unitStmt->execute();
            $unitId = (int) ($unitStmt->fetchColumn() ?: 1);

            // Mevcut stok kontrolü
            $balStmt = $pdo->prepare("SELECT net_qty FROM stock_balances WHERE product_id=? AND location_id=?");
            $balStmt->execute([$productId, $locationId]);
            $current = (float) ($balStmt->fetchColumn() ?: 0);

            if ($current < $qty) {
                throw new \RuntimeException("Yetersiz stok: mevcut {$current} adet, istek {$qty} adet.");
            }

            $pdo->prepare("
                INSERT INTO stock_outlets
                    (outlet_type_id, product_id, location_id, quantity, unit_id,
                     unit_cost, outlet_date, reference_note, created_by)
                VALUES (?, ?, ?, ?, ?, 0, CURDATE(), ?, ?)
            ")->execute([$otId, $productId, $locationId, $qty, $unitId, $note ?: null, $createdBy]);

            $outletId = (int) $pdo->lastInsertId();

            // Bakiyeden düş
            $pdo->prepare("
                UPDATE stock_balances
                SET net_qty = GREATEST(0, net_qty - ?),
                    gross_qty = GREATEST(0, gross_qty - ?)
                WHERE product_id=? AND location_id=?
            ")->execute([$qty, $qty, $productId, $locationId]);

            AuditManager::log('stock_outlets', $outletId, 'INSERT', null, [
                'type' => $outletTypeCode, 'product_id' => $productId, 'qty' => $qty,
            ]);

            return $outletId;
        });
    }

    // ── Yeni demirbaş alımı / iade girişi ─────────────────

    public function addStock(
        int    $productId,
        float  $qty,
        int    $locationId,
        int    $createdBy,
        float  $unitPrice = 0,
        string $note = ''
    ): int {
        return Database::transaction(function (\PDO $pdo) use (
            $productId, $qty, $locationId, $createdBy, $unitPrice, $note
        ) {
            $etStmt = $pdo->prepare("SELECT id FROM entry_types WHERE code='OFFICIAL_PURCHASE'");
            $etStmt->execute();
            $etId = (int) $etStmt->fetchColumn();

            $unitStmt = $pdo->prepare("SELECT id FROM units WHERE abbreviation='adet' LIMIT 1");
            $unitStmt->execute();
            $unitId = (int) ($unitStmt->fetchColumn() ?: 1);

            $pdo->prepare("
                INSERT INTO stock_entries
                    (entry_type_id, product_id, location_id, quantity_gross, quantity_net,
                     unit_id, unit_price, entry_date, reference_note, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?)
            ")->execute([$etId, $productId, $locationId, $qty, $qty, $unitId, $unitPrice, $note ?: null, $createdBy]);

            $entryId = (int) $pdo->lastInsertId();

            // WAC güncelle (demirbaş için basit topla)
            $pdo->prepare("
                INSERT INTO stock_balances (product_id, location_id, net_qty, gross_qty, avg_cost)
                VALUES (:pid, :lid, :qty, :qty, :cost)
                ON DUPLICATE KEY UPDATE
                    net_qty   = net_qty   + :qty2,
                    gross_qty = gross_qty + :qty3
            ")->execute([
                ':pid'  => $productId, ':lid'  => $locationId,
                ':qty'  => $qty,       ':cost' => $unitPrice,
                ':qty2' => $qty,       ':qty3' => $qty,
            ]);

            return $entryId;
        });
    }

    // ── Bu ay kırılma / kayıp geçmişi ─────────────────────

    public function getLossHistory(int $locationId = 0, int $months = 1): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                p.name                 AS product_name,
                ot.name                AS loss_type,
                ot.code                AS loss_code,
                so.quantity,
                so.outlet_date,
                so.reference_note,
                u.name                 AS user_name
            FROM stock_outlets so
            JOIN outlet_types ot ON ot.id = so.outlet_type_id
                AND ot.code IN ('BREAKAGE','ASSET_LOST')
            JOIN products p    ON p.id  = so.product_id
            JOIN categories c  ON c.id  = p.category_id AND c.product_type = 'fixed_asset'
            JOIN users u       ON u.id  = so.created_by
            WHERE so.deleted_at IS NULL
              AND so.outlet_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            ORDER BY so.outlet_date DESC, so.created_at DESC
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    // ── Lokasyona göre özet (mutfak vs. bar vs. depo) ─────

    public function getByLocation(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                sl.name   AS location_name,
                sl.type   AS location_type,
                p.name    AS product_name,
                p.id      AS product_id,
                COALESCE(sb.net_qty, 0) AS qty
            FROM stock_locations sl
            JOIN stock_balances sb  ON sb.location_id = sl.id
            JOIN products p         ON p.id = sb.product_id
            JOIN categories c       ON c.id = p.category_id AND c.product_type = 'fixed_asset'
            WHERE sl.is_active = 1 AND p.deleted_at IS NULL AND sb.net_qty > 0
            ORDER BY sl.name, p.name
        ");
        $rows   = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['location_name']][] = $row;
        }
        return $result;
    }
}
