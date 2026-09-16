<?php
declare(strict_types=1);

namespace Mudavim\Core;

use InvalidArgumentException;
use RuntimeException;

/**
 * Sistemin kalbi: tüm stok giriş/çıkış işlemleri buradan geçer.
 *
 * Her işlem bir database transaction içinde çalışır.
 * stock_balances (WAC), cari_transactions ve cash_transactions
 * otomatik olarak güncellenir.
 */
class StockManager
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    // =========================================================
    // STOK GİRİŞİ
    // =========================================================

    /**
     * @param array $data {
     *   entry_type_code: string,
     *   product_id: int,
     *   location_id: int,
     *   quantity_gross: float,   // Satın alınan miktar (alım birimi)
     *   unit_id: int,
     *   unit_price: float,
     *   supplier_id?: int,
     *   invoice_number?: string,
     *   reference_note?: string,
     *   entry_date?: string,     // Y-m-d
     *   pin?: string,            // PIN korumalı işlemler için
     * }
     */
    public function recordEntry(array $data, int $createdBy): array
    {
        return Database::transaction(function (\PDO $pdo) use ($data, $createdBy) {

            $entryType = $this->getEntryType($data['entry_type_code']);

            // PIN doğrulama
            if ($entryType['requires_pin']) {
                $this->verifyPin($createdBy, $data['pin'] ?? '');
            }

            $product    = $this->getProduct($data['product_id']);
            $grossQty   = (float) $data['quantity_gross'];
            $netQty     = round($grossQty * ($product['yield_percentage'] / 100), 4);
            $unitPrice  = (float) ($data['unit_price'] ?? 0);
            $entryDate  = $data['entry_date'] ?? date('Y-m-d');

            // Stok girişi yaz
            $stmt = $pdo->prepare("
                INSERT INTO stock_entries
                    (entry_type_id, product_id, location_id, supplier_id,
                     quantity_gross, quantity_net, unit_id,
                     unit_price, tax_amount,
                     invoice_number, reference_note,
                     entry_date, created_by)
                VALUES
                    (:et, :pid, :lid, :sid,
                     :qg, :qn, :uid,
                     :up, :tax,
                     :inv, :ref,
                     :edate, :cb)
            ");
            $stmt->execute([
                ':et'    => $entryType['id'],
                ':pid'   => $product['id'],
                ':lid'   => $data['location_id'],
                ':sid'   => $data['supplier_id'] ?? null,
                ':qg'    => $grossQty,
                ':qn'    => $netQty,
                ':uid'   => $data['unit_id'],
                ':up'    => $unitPrice,
                ':tax'   => $data['tax_amount'] ?? 0,
                ':inv'   => $data['invoice_number'] ?? null,
                ':ref'   => $data['reference_note'] ?? null,
                ':edate' => $entryDate,
                ':cb'    => $createdBy,
            ]);
            $entryId = (int) $pdo->lastInsertId();

            // Stok bakiyesini WAC (Weighted Average Cost) ile güncelle
            $this->updateBalance($product['id'], (int) $data['location_id'], $netQty, $unitPrice, 'add');

            // Kasa hareketi
            $cashTxnId = null;
            if ($entryType['affects_cash'] && $unitPrice > 0) {
                $cashTxnId = $this->recordCashTransaction([
                    'type'           => 'purchase',
                    'amount'         => -($grossQty * $unitPrice), // çıkış
                    'description'    => "Stok girişi #{$entryId}: {$product['name']}",
                    'reference_type' => 'stock_entry',
                    'reference_id'   => $entryId,
                    'transaction_date' => $entryDate,
                ], $createdBy);
            }

            // Cari hareket
            $cariTxnId = null;
            if ($entryType['affects_cari']) {
                $cariId    = $data['cari_id'] ?? $this->getPatronCariId();
                $direction = (int) $entryType['cari_direction'];
                $amount    = $direction * ($grossQty * $unitPrice);

                $cariTxnId = $this->recordCariTransaction([
                    'cari_id'          => $cariId,
                    'amount'           => $amount,
                    'description'      => "Stok girişi #{$entryId}: {$product['name']}",
                    'reference_type'   => 'stock_entry',
                    'reference_id'     => $entryId,
                    'transaction_date' => $entryDate,
                ], $createdBy);

                // Referansları geri yaz
                $pdo->prepare("UPDATE stock_entries SET related_cari_id=?, related_cash_txn_id=? WHERE id=?")
                    ->execute([$cariTxnId, $cashTxnId, $entryId]);
            }

            AuditManager::log('stock_entries', $entryId, 'INSERT', null, array_merge($data, ['id' => $entryId]));

            // Fire logu: eğer yield %100 değilse zayi çıkışı yaz
            if ($product['yield_percentage'] < 100) {
                $fireQty = round($grossQty - $netQty, 4);
                $this->recordFireWaste($product['id'], (int) $data['location_id'], $fireQty, $data['unit_id'], $unitPrice, $createdBy, $entryDate);
            }

            // Event hook tetikle
            $this->fireStockEvents($product['id'], (int) $data['location_id']);

            return [
                'entry_id'    => $entryId,
                'quantity_net' => $netQty,
                'cash_txn_id' => $cashTxnId,
                'cari_txn_id' => $cariTxnId,
            ];
        });
    }

    // =========================================================
    // STOK ÇIKIŞI
    // =========================================================

    /**
     * @param array $data {
     *   outlet_type_code: string,
     *   product_id: int,
     *   location_id: int,
     *   quantity: float,
     *   unit_id: int,
     *   outlet_date?: string,
     *   table_number?: string,
     *   recipe_id?: int,
     *   reference_note?: string,
     * }
     */
    public function recordOutlet(array $data, int $createdBy): array
    {
        return Database::transaction(function (\PDO $pdo) use ($data, $createdBy) {

            $outletType = $this->getOutletType($data['outlet_type_code']);
            $product    = $this->getProduct($data['product_id']);
            $qty        = (float) $data['quantity'];
            $outletDate = $data['outlet_date'] ?? date('Y-m-d');

            // Stok yeterli mi? (Patron çekimi ve zayi için negatife izin yok)
            $balance = $this->getBalance($product['id'], (int) $data['location_id']);
            if ($balance['net_qty'] < $qty && !in_array($outletType['code'], ['COUNT_ADJUSTMENT_DN'])) {
                throw new RuntimeException(
                    "Yetersiz stok: {$product['name']} — Mevcut: {$balance['net_qty']}, İstenen: {$qty}"
                );
            }

            $unitCost = (float) ($balance['avg_cost'] ?? $product['last_purchase_price']);

            $stmt = $pdo->prepare("
                INSERT INTO stock_outlets
                    (outlet_type_id, product_id, location_id, quantity, unit_id,
                     unit_cost, outlet_date, table_number, recipe_id,
                     reference_note, created_by)
                VALUES
                    (:ot, :pid, :lid, :qty, :uid,
                     :uc, :odate, :tn, :rid,
                     :ref, :cb)
            ");
            $stmt->execute([
                ':ot'    => $outletType['id'],
                ':pid'   => $product['id'],
                ':lid'   => $data['location_id'],
                ':qty'   => $qty,
                ':uid'   => $data['unit_id'],
                ':uc'    => $unitCost,
                ':odate' => $outletDate,
                ':tn'    => $data['table_number'] ?? null,
                ':rid'   => $data['recipe_id'] ?? null,
                ':ref'   => $data['reference_note'] ?? null,
                ':cb'    => $createdBy,
            ]);
            $outletId = (int) $pdo->lastInsertId();

            // Bakiye düş
            $this->updateBalance($product['id'], (int) $data['location_id'], $qty, $unitCost, 'subtract');

            // Patron Nakit Çekimi → kasa ve cari
            if ($outletType['code'] === 'PATRON_CASH_WITHDRAW') {
                $amount = $qty * $unitCost; // Burada qty = TL tutarı olmalı, ürün miktarı değil
                $this->recordCashTransaction([
                    'type'             => 'patron_withdraw',
                    'amount'           => -$amount,
                    'description'      => "Patron nakit çekimi #{$outletId}",
                    'reference_type'   => 'stock_outlet',
                    'reference_id'     => $outletId,
                    'transaction_date' => $outletDate,
                ], $createdBy);

                $this->recordCariTransaction([
                    'cari_id'          => $this->getPatronCariId(),
                    'amount'           => -$amount, // Borç kapandı
                    'description'      => "Patron nakit çekimi #{$outletId}",
                    'reference_type'   => 'stock_outlet',
                    'reference_id'     => $outletId,
                    'transaction_date' => $outletDate,
                ], $createdBy);
            }

            // Patron Eve Götürdü → sadece cari
            if ($outletType['code'] === 'PATRON_DRAWING') {
                $amount = $qty * $unitCost;
                $this->recordCariTransaction([
                    'cari_id'          => $this->getPatronCariId(),
                    'amount'           => -$amount,
                    'description'      => "Patron çıkışı #{$outletId}: {$product['name']}",
                    'reference_type'   => 'stock_outlet',
                    'reference_id'     => $outletId,
                    'transaction_date' => $outletDate,
                ], $createdBy);
            }

            AuditManager::log('stock_outlets', $outletId, 'INSERT', null, array_merge($data, ['id' => $outletId]));

            // Günlük tüketim geçmişini güncelle (Velocity için)
            $this->updateConsumptionHistory($product['id'], (int) $data['location_id'], $qty, $data['unit_id'], $outletDate);

            // Event hook tetikle
            $this->fireStockEvents($product['id'], (int) $data['location_id']);

            return ['outlet_id' => $outletId, 'unit_cost' => $unitCost, 'total_cost' => $qty * $unitCost];
        });
    }

    // =========================================================
    // REÇETE DÜŞÜMÜ (Satış entegrasyonu)
    // =========================================================

    public function deductByRecipe(int $recipeId, float $portions, int $locationId, int $createdBy, ?string $tableNumber = null): array
    {
        $ingredients = $this->pdo->prepare("
            SELECT ri.*, p.name, p.yield_percentage, p.id AS pid
            FROM recipe_ingredients ri
            JOIN products p ON p.id = ri.product_id
            WHERE ri.recipe_id = ?
            ORDER BY ri.sort_order
        ");
        $ingredients->execute([$recipeId]);
        $items = $ingredients->fetchAll();

        $results = [];
        foreach ($items as $item) {
            $qty = round($item['quantity'] * $portions, 4);
            // Reçete net miktarı kullanır; fire zaten uygulanmış demek
            $results[] = $this->recordOutlet([
                'outlet_type_code' => 'SALE',
                'product_id'       => $item['pid'],
                'location_id'      => $locationId,
                'quantity'         => $qty,
                'unit_id'          => $item['unit_id'],
                'recipe_id'        => $recipeId,
                'table_number'     => $tableNumber,
            ], $createdBy);
        }
        return $results;
    }

    // =========================================================
    // BAKİYE GÜNCELLEMESİ (WAC — Ağırlıklı Ortalama Maliyet)
    // =========================================================

    private function updateBalance(int $productId, int $locationId, float $qty, float $unitCost, string $direction): void
    {
        if ($direction === 'add') {
            $this->pdo->prepare("
                INSERT INTO stock_balances (product_id, location_id, gross_qty, net_qty, avg_cost)
                VALUES (:pid, :lid, :gqty, :nqty, :cost)
                ON DUPLICATE KEY UPDATE
                    avg_cost  = ((net_qty * avg_cost) + (:qty2 * :cost2)) / (net_qty + :qty3),
                    gross_qty = gross_qty + :qty4,
                    net_qty   = net_qty   + :qty5
            ")->execute([
                ':pid'   => $productId, ':lid'   => $locationId,
                ':gqty'  => $qty,       ':nqty'  => $qty,       ':cost'  => $unitCost,
                ':qty2'  => $qty,       ':cost2' => $unitCost,
                ':qty3'  => $qty,       ':qty4'  => $qty,
                ':qty5'  => $qty,
            ]);
        } else {
            $this->pdo->prepare("
                UPDATE stock_balances
                SET net_qty   = GREATEST(0, net_qty - :qty),
                    gross_qty = GREATEST(0, gross_qty - :qty2)
                WHERE product_id = :pid AND location_id = :lid
            ")->execute([
                ':qty'  => $qty, ':qty2' => $qty,
                ':pid'  => $productId, ':lid' => $locationId,
            ]);
        }
    }

    // =========================================================
    // YARDIMCI FONKSİYONLAR
    // =========================================================

    private function getEntryType(string $code): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entry_types WHERE code = ?");
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) throw new InvalidArgumentException("Geçersiz giriş tipi: {$code}");
        return $row;
    }

    private function getOutletType(string $code): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM outlet_types WHERE code = ?");
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        if (!$row) throw new InvalidArgumentException("Geçersiz çıkış tipi: {$code}");
        return $row;
    }

    private function getProduct(int $id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE id = ? AND deleted_at IS NULL");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) throw new InvalidArgumentException("Ürün bulunamadı: #{$id}");
        return $row;
    }

    private function getBalance(int $productId, int $locationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT COALESCE(net_qty, 0) AS net_qty, COALESCE(avg_cost, 0) AS avg_cost
            FROM stock_balances WHERE product_id = ? AND location_id = ?
        ");
        $stmt->execute([$productId, $locationId]);
        return $stmt->fetch() ?: ['net_qty' => 0, 'avg_cost' => 0];
    }

    private function getPatronCariId(): int
    {
        $stmt = $this->pdo->query("SELECT id FROM cari_accounts WHERE type='patron' LIMIT 1");
        $row  = $stmt->fetch();
        if (!$row) throw new RuntimeException("Patron cari hesabı tanımlı değil.");
        return (int) $row['id'];
    }

    private function verifyPin(int $userId, string $pin): void
    {
        $stmt = $this->pdo->prepare("SELECT pin_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($pin, $row['pin_hash'] ?? '')) {
            throw new RuntimeException("PIN doğrulaması başarısız.");
        }
    }

    private function recordCashTransaction(array $data, int $createdBy): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO cash_transactions
                (register_id, type, amount, description, reference_type, reference_id, transaction_date, created_by)
            VALUES
                (1, :type, :amount, :desc, :rtype, :rid, :date, :cb)
        ");
        $stmt->execute([
            ':type'   => $data['type'],
            ':amount' => $data['amount'],
            ':desc'   => $data['description'],
            ':rtype'  => $data['reference_type'] ?? null,
            ':rid'    => $data['reference_id'] ?? null,
            ':date'   => $data['transaction_date'],
            ':cb'     => $createdBy,
        ]);
        // Kasa bakiyesini güncelle
        $this->pdo->prepare("UPDATE cash_registers SET current_balance = current_balance + ? WHERE id = 1")
            ->execute([$data['amount']]);
        return (int) $this->pdo->lastInsertId();
    }

    private function recordCariTransaction(array $data, int $createdBy): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO cari_transactions
                (cari_id, amount, description, reference_type, reference_id, transaction_date, created_by)
            VALUES
                (:cari, :amount, :desc, :rtype, :rid, :date, :cb)
        ");
        $stmt->execute([
            ':cari'   => $data['cari_id'],
            ':amount' => $data['amount'],
            ':desc'   => $data['description'],
            ':rtype'  => $data['reference_type'] ?? null,
            ':rid'    => $data['reference_id'] ?? null,
            ':date'   => $data['transaction_date'],
            ':cb'     => $createdBy,
        ]);
        // Cari bakiyeyi güncelle
        $this->pdo->prepare("UPDATE cari_accounts SET balance = balance + ? WHERE id = ?")
            ->execute([$data['amount'], $data['cari_id']]);
        return (int) $this->pdo->lastInsertId();
    }

    private function recordFireWaste(int $productId, int $locationId, float $qty, int $unitId, float $unitCost, int $createdBy, string $date): void
    {
        $this->recordOutlet([
            'outlet_type_code' => 'PREP_WASTE',
            'product_id'       => $productId,
            'location_id'      => $locationId,
            'quantity'         => $qty,
            'unit_id'          => $unitId,
            'outlet_date'      => $date,
            'reference_note'   => 'Otomatik fire logu',
        ], $createdBy);
    }

    private function updateConsumptionHistory(int $productId, int $locationId, float $qty, int $unitId, string $date): void
    {
        $ts  = strtotime($date);
        $dow = (int) date('w', $ts);
        $wk  = (int) date('W', $ts);
        $mn  = (int) date('n', $ts);

        $this->pdo->prepare("
            INSERT INTO consumption_history
                (product_id, location_id, period_date, day_of_week, week_number, month_number, quantity_used, unit_id)
            VALUES (:pid, :lid, :dt, :dow, :wk, :mn, :qty, :uid)
            ON DUPLICATE KEY UPDATE quantity_used = quantity_used + :qty2
        ")->execute([
            ':pid' => $productId, ':lid' => $locationId, ':dt' => $date,
            ':dow' => $dow, ':wk' => $wk, ':mn' => $mn,
            ':qty' => $qty, ':uid' => $unitId, ':qty2' => $qty,
        ]);
    }

    private function fireStockEvents(int $productId, int $locationId): void
    {
        $balance = $this->getBalance($productId, $locationId);
        $product = $this->getProduct($productId);

        if ($balance['net_qty'] <= 0) {
            $this->insertAlert($productId, 'negative_stock', $balance['net_qty'], null);
            return;
        }
        if ($balance['net_qty'] <= $product['min_stock_qty']) {
            $velocity   = $this->calculateVelocity($productId, $locationId);
            $daysLeft   = $velocity > 0 ? round($balance['net_qty'] / $velocity, 1) : null;
            $alertType  = $balance['net_qty'] <= ($product['min_stock_qty'] * 0.5) ? 'critical_stock' : 'low_stock';
            $this->insertAlert($productId, $alertType, $balance['net_qty'], $daysLeft);
        }
    }

    private function insertAlert(int $productId, string $type, float $currentQty, ?float $daysLeft): void
    {
        // Aynı türde aktif uyarı varsa tekrar ekleme
        $exists = $this->pdo->prepare("
            SELECT id FROM stock_alerts WHERE product_id=? AND alert_type=? AND is_acknowledged=0
        ");
        $exists->execute([$productId, $type]);
        if ($exists->fetch()) return;

        $this->pdo->prepare("
            INSERT INTO stock_alerts (product_id, alert_type, current_qty, days_remaining)
            VALUES (?, ?, ?, ?)
        ")->execute([$productId, $type, $currentQty, $daysLeft]);
    }

    /**
     * Be Truly Velocity: Haftanın aynı günlerine ağırlık verir (son 4 hafta).
     * Düz "son 3 gün ortalaması" değil — sahil kasabası için daha gerçekçi.
     */
    public function calculateVelocity(int $productId, int $locationId, int $weeks = 4): float
    {
        $stmt = $this->pdo->prepare("
            SELECT
                AVG(quantity_used) AS avg_usage,
                day_of_week
            FROM consumption_history
            WHERE product_id = ?
              AND location_id = ?
              AND period_date >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
            GROUP BY day_of_week
        ");
        $stmt->execute([$productId, $locationId, $weeks]);
        $rows = $stmt->fetchAll();
        if (empty($rows)) return 0.0;

        // Ağırlıklı ortalama: bu haftaki günler için gerçek ortalamayı kullan
        $total  = array_sum(array_column($rows, 'avg_usage'));
        $count  = count($rows);
        return round($total / $count, 4);
    }
}
