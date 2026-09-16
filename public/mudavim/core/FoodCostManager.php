<?php
declare(strict_types=1);

namespace Mudavim\Core;

/**
 * Canlı Food Cost hesaplama ve kar marjı uyarısı.
 * Reçetedeki her malzeme için anlık WAC maliyet kullanılır.
 */
class FoodCostManager
{
    private \PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::get();
    }

    /**
     * Reçetenin anlık maliyetini hesaplar.
     * @return array {
     *   recipe_id, recipe_name, menu_price,
     *   total_cost, food_cost_pct, margin_pct,
     *   ingredients: [{name, qty, unit, unit_cost, line_cost}],
     *   alert: bool
     * }
     */
    public function calculateRecipeCost(int $recipeId): array
    {
        $recipe = $this->pdo->prepare("SELECT * FROM recipes WHERE id = ?");
        $recipe->execute([$recipeId]);
        $rec = $recipe->fetch();
        if (!$rec) throw new \InvalidArgumentException("Reçete bulunamadı: #{$recipeId}");

        $ingStmt = $this->pdo->prepare("
            SELECT
                ri.quantity,
                ri.apply_yield,
                p.name AS product_name,
                p.yield_percentage,
                u.abbreviation AS unit_abbr,
                COALESCE(sb.avg_cost, p.last_purchase_price) AS unit_cost
            FROM recipe_ingredients ri
            JOIN products p  ON p.id = ri.product_id
            JOIN units    u  ON u.id = ri.unit_id
            LEFT JOIN stock_balances sb ON sb.product_id = ri.product_id
            WHERE ri.recipe_id = ?
            ORDER BY ri.sort_order
        ");
        $ingStmt->execute([$recipeId]);
        $ingredients = $ingStmt->fetchAll();

        $totalCost   = 0;
        $breakdown   = [];

        foreach ($ingredients as $ing) {
            $qty = (float) $ing['quantity'];
            // Be Truly: Fire reçete seviyesinde uygulanır
            if ($ing['apply_yield'] && $ing['yield_percentage'] < 100) {
                $qty = round($qty / ($ing['yield_percentage'] / 100), 4);
            }
            $lineCost     = round($qty * (float) $ing['unit_cost'], 4);
            $totalCost   += $lineCost;
            $breakdown[]  = [
                'name'      => $ing['product_name'],
                'qty'       => $qty,
                'unit'      => $ing['unit_abbr'],
                'unit_cost' => $ing['unit_cost'],
                'line_cost' => $lineCost,
            ];
        }

        $menuPrice     = (float) $rec['menu_price'];
        $foodCostPct   = $menuPrice > 0 ? round(($totalCost / $menuPrice) * 100, 2) : null;
        $marginPct     = $menuPrice > 0 ? round((1 - $totalCost / $menuPrice) * 100, 2) : null;

        // Uyarı: Food cost %35'i geçtiyse (ayarlanabilir)
        $alertThreshold = 35.0;
        $alert          = $foodCostPct !== null && $foodCostPct > $alertThreshold;

        return [
            'recipe_id'      => $recipeId,
            'recipe_name'    => $rec['name'],
            'menu_price'     => $menuPrice,
            'total_cost'     => round($totalCost, 2),
            'food_cost_pct'  => $foodCostPct,
            'margin_pct'     => $marginPct,
            'ingredients'    => $breakdown,
            'alert'          => $alert,
            'alert_msg'      => $alert ? "Food cost %{$foodCostPct} — Kritik eşik (%{$alertThreshold}) aşıldı!" : null,
        ];
    }

    /**
     * Tüm aktif reçetelerin özetini döndürür.
     * Kar marjı en düşükten en yükseğe sıralanır.
     */
    public function getAllRecipeCosts(): array
    {
        $stmt = $this->pdo->query("SELECT id FROM recipes WHERE is_active = 1 AND deleted_at IS NULL");
        $ids  = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        $results = [];
        foreach ($ids as $id) {
            try {
                $results[] = $this->calculateRecipeCost((int) $id);
            } catch (\Throwable) {
                // Malzeme tanımsızsa atla
            }
        }
        usort($results, fn($a, $b) => $a['margin_pct'] <=> $b['margin_pct']);
        return $results;
    }

    /**
     * Gerçek Gider Raporu — ay sonu özet.
     * Muhasebe değil, sahada ne harcandı raporu.
     */
    public function getExpenseReport(string $startDate, string $endDate): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                ot.accounting_category,
                ot.name AS outlet_type_name,
                COUNT(so.id)          AS transaction_count,
                SUM(so.quantity)      AS total_qty,
                SUM(so.total_cost)    AS total_value
            FROM stock_outlets so
            JOIN outlet_types ot ON ot.id = so.outlet_type_id
            WHERE so.outlet_date BETWEEN :s AND :e
              AND so.deleted_at IS NULL
            GROUP BY ot.accounting_category, ot.id
            ORDER BY ot.accounting_category, total_value DESC
        ");
        $stmt->execute([':s' => $startDate, ':e' => $endDate]);
        $rows = $stmt->fetchAll();

        $grouped = [];
        foreach ($rows as $row) {
            $cat = $row['accounting_category'];
            if (!isset($grouped[$cat])) $grouped[$cat] = ['total' => 0, 'lines' => []];
            $grouped[$cat]['total'] += $row['total_value'];
            $grouped[$cat]['lines'][] = $row;
        }

        // Nakit hareketleri ekle
        $cashStmt = $this->pdo->prepare("
            SELECT type, SUM(ABS(amount)) AS total
            FROM cash_transactions
            WHERE transaction_date BETWEEN :s AND :e
              AND amount < 0
            GROUP BY type
        ");
        $cashStmt->execute([':s' => $startDate, ':e' => $endDate]);
        $cashRows = $cashStmt->fetchAll();

        return [
            'period'       => ['start' => $startDate, 'end' => $endDate],
            'by_category'  => $grouped,
            'cash_outflows' => $cashRows,
        ];
    }
}
