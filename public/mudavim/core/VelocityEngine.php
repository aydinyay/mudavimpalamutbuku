<?php
declare(strict_types=1);

namespace Mudavim\Core;

/**
 * Be Truly Velocity Engine:
 * Düz "son 3 gün ortalaması" değil;
 * - Haftanın aynı günlerine ağırlık
 * - Sezonluk ay bazlı katsayı
 * - Kapasiteye göre dinamik tahmin (rezervasyon sayısı varsa)
 */
class VelocityEngine
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    /**
     * @param int $productId
     * @param int $locationId
     * @param int|null $upcomingCovers  Rezervasyondan gelen kişi sayısı (opsiyonel)
     * @return array {
     *   product_id, product_name, current_qty, daily_velocity,
     *   days_remaining, reorder_suggested, shopping_qty,
     *   velocity_confidence: 'low'|'medium'|'high'
     * }
     */
    public function forecast(int $productId, int $locationId, ?int $upcomingCovers = null): array
    {
        $product = $this->pdo->prepare("
            SELECT p.*, sb.net_qty, sb.avg_cost
            FROM products p
            LEFT JOIN stock_balances sb ON sb.product_id = p.id AND sb.location_id = ?
            WHERE p.id = ?
        ");
        $product->execute([$locationId, $productId]);
        $prod = $product->fetch();
        if (!$prod) throw new \InvalidArgumentException("Ürün bulunamadı: #{$productId}");

        $todayDow   = (int) date('w');   // 0=Pazar
        $todayMonth = (int) date('n');

        // Haftanın aynı günü bazlı ortalama (son 8 hafta)
        $dowVelocity = $this->getDowVelocity($productId, $locationId, $todayDow, 8);

        // Bu ay için tarihsel ortalama
        $monthlyVelocity = $this->getMonthlyVelocity($productId, $locationId, $todayMonth, 2);

        // Güven skoru: kaç veri noktası var?
        $dataPoints = $this->countDataPoints($productId, $locationId);
        $confidence = match(true) {
            $dataPoints >= 30 => 'high',
            $dataPoints >= 10 => 'medium',
            default           => 'low',
        };

        // Karma tahmin: DoW ağırlığı %60, aylık %40
        $baseVelocity = ($dowVelocity * 0.6) + ($monthlyVelocity * 0.4);

        // Rezervasyon katsayısı: eğer bugünün rezervasyonu mevcutsa
        if ($upcomingCovers !== null && $upcomingCovers > 0) {
            $avgCovers    = $this->getAverageCoverForDow($productId, $locationId, $todayDow);
            $coverRatio   = $avgCovers > 0 ? ($upcomingCovers / $avgCovers) : 1;
            $baseVelocity = round($baseVelocity * $coverRatio, 4);
        }

        $currentQty     = max(0, (float) ($prod['net_qty'] ?? 0));
        $daysRemaining  = $baseVelocity > 0 ? round($currentQty / $baseVelocity, 1) : null;
        $reorderPoint   = (float) $prod['reorder_qty'];
        $minStock       = (float) $prod['min_stock_qty'];

        // Tedarikçi lead time (varsayılan 1 gün)
        $leadDays = $this->getLeadDays($productId, (int) ($prod['default_supplier_id'] ?? 0));

        // Sipariş ver: kalan gün ≤ lead time + 1 güvenlik
        $shouldReorder  = $daysRemaining !== null && $daysRemaining <= ($leadDays + 1);
        $shoppingQty    = max(0, $reorderPoint - $currentQty);

        return [
            'product_id'          => $productId,
            'product_name'        => $prod['name'],
            'current_qty'         => $currentQty,
            'daily_velocity'      => round($baseVelocity, 3),
            'days_remaining'      => $daysRemaining,
            'lead_days'           => $leadDays,
            'reorder_suggested'   => $shouldReorder,
            'shopping_qty'        => $shouldReorder ? round($shoppingQty, 2) : 0,
            'velocity_confidence' => $confidence,
            'data_points'         => $dataPoints,
        ];
    }

    /**
     * Tüm ürünler için alışveriş listesi — tedarikçiye göre gruplu.
     */
    public function generateShoppingList(int $locationId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.default_supplier_id, s.name AS supplier_name
            FROM products p
            LEFT JOIN suppliers s ON s.id = p.default_supplier_id
            WHERE p.is_tracked = 1 AND p.deleted_at IS NULL
            ORDER BY s.name, p.name
        ");
        $stmt->execute();
        $products = $stmt->fetchAll();

        $list = [];
        foreach ($products as $prod) {
            $forecast = $this->forecast((int) $prod['id'], $locationId);
            if (!$forecast['reorder_suggested'] || $forecast['shopping_qty'] <= 0) continue;

            $supplierKey = $prod['supplier_name'] ?? 'Tedarikçisiz';
            $list[$supplierKey][] = [
                'product'    => $forecast['product_name'],
                'order_qty'  => $forecast['shopping_qty'],
                'days_left'  => $forecast['days_remaining'],
                'confidence' => $forecast['velocity_confidence'],
            ];
        }
        return $list;
    }

    private function getDowVelocity(int $productId, int $locationId, int $dow, int $weeks): float
    {
        $stmt = $this->pdo->prepare("
            SELECT AVG(quantity_used) AS avg_use
            FROM consumption_history
            WHERE product_id  = ?
              AND location_id = ?
              AND day_of_week = ?
              AND period_date >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
        ");
        $stmt->execute([$productId, $locationId, $dow, $weeks]);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    private function getMonthlyVelocity(int $productId, int $locationId, int $month, int $yearLookback): float
    {
        $stmt = $this->pdo->prepare("
            SELECT AVG(quantity_used) AS avg_use
            FROM consumption_history
            WHERE product_id   = ?
              AND location_id  = ?
              AND month_number = ?
              AND period_date  >= DATE_SUB(CURDATE(), INTERVAL ? YEAR)
        ");
        $stmt->execute([$productId, $locationId, $month, $yearLookback]);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    private function countDataPoints(int $productId, int $locationId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM consumption_history
            WHERE product_id = ? AND location_id = ?
        ");
        $stmt->execute([$productId, $locationId]);
        return (int) $stmt->fetchColumn();
    }

    private function getAverageCoverForDow(int $productId, int $locationId, int $dow): float
    {
        $stmt = $this->pdo->prepare("
            SELECT AVG(cover_count)
            FROM consumption_history
            WHERE product_id = ? AND location_id = ? AND day_of_week = ? AND cover_count > 0
        ");
        $stmt->execute([$productId, $locationId, $dow]);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    private function getLeadDays(int $productId, int $supplierId): int
    {
        if (!$supplierId) return 1;
        $stmt = $this->pdo->prepare("
            SELECT lead_days FROM supplier_products
            WHERE product_id = ? AND supplier_id = ? AND is_preferred = 1 LIMIT 1
        ");
        $stmt->execute([$productId, $supplierId]);
        return (int) ($stmt->fetchColumn() ?? 1);
    }
}
