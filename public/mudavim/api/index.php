<?php
declare(strict_types=1);

/**
 * Mudavim v4 — RESTful API Entry Point
 * Tüm istekler buradan yönlendirilir.
 * Header: Content-Type: application/json
 * Auth:   Bearer token (JWT veya session-based — sonraki aşama)
 */

require_once __DIR__ . '/../autoload.php';

use Mudavim\Core\{Database, AuditManager, Auth};

header('Content-Type: application/json; charset=utf-8');
// CORS: sadece aynı origin'den istek kabul et (production güvenliği)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
}
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Session Auth ---
Auth::boot();
if (!Auth::check()) {
    http_response_code(401);
    echo json_encode(['error' => 'Oturum açmanız gerekiyor.']);
    exit;
}
$userId     = Auth::id();
$userRole   = Auth::role();
$locationId = Auth::locationId();
AuditManager::init($userId, $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

// --- Routing ---
$fullPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$apiPos   = strpos($fullPath, 'api/');
$path     = $apiPos !== false ? substr($fullPath, $apiPos + 4) : $fullPath;
$parts    = explode('/', $path);
$module   = $parts[0] ?? '';   // 'stock', 'auth', 'assets' ...
$entity   = $parts[1] ?? '';   // 'entries', 'verify-pin' ...
$id       = isset($parts[2]) ? (int) $parts[2] : null;

$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

function respond(mixed $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode(['status' => $code < 400 ? 'ok' : 'error', 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function respondError(string $message, int $code = 400): never
{
    respond(['message' => $message], $code);
}

try {
    match ("{$module}/{$entity}") {

        // --- STOK GİRİŞLERİ ---
        'stock/entries' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                $mgr    = new \Mudavim\Core\StockManager();
                $result = $mgr->recordEntry($body, $userId);

                // Tedarikçi seçilmişse otomatik cari borç yaz
                if (!empty($body['supplier_id'])) {
                    $supplierId = (int) $body['supplier_id'];
                    $pdo        = Database::get();
                    $supRow     = $pdo->prepare("SELECT cari_id, name FROM suppliers WHERE id=? AND deleted_at IS NULL");
                    $supRow->execute([$supplierId]);
                    $sup    = $supRow->fetch();
                    $cariId = $sup['cari_id'] ?? null;

                    if ($cariId) {
                        $unitPrice   = (float)($body['unit_price'] ?? 0);
                        $qty         = (float)($body['quantity_gross'] ?? 0);
                        $totalAmount = round($unitPrice * $qty, 2);

                        if ($totalAmount > 0) {
                            $pdo->prepare("
                                INSERT INTO cari_transactions
                                    (cari_id, amount, description, reference_type, reference_id, transaction_date, created_by)
                                VALUES (?, ?, ?, 'stock_entry', ?, CURDATE(), ?)
                            ")->execute([
                                $cariId,
                                $totalAmount,
                                'Stok alımı #' . ($result['entry_id'] ?? ''),
                                $result['entry_id'] ?? null,
                                $userId,
                            ]);
                            $pdo->prepare("UPDATE cari_accounts SET balance = balance + ? WHERE id=?")
                                ->execute([$totalAmount, $cariId]);
                        }
                    }
                }

                respond($result, 201);
            })(),
            'GET'  => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->prepare("
                    SELECT se.*, p.name AS product_name, et.name AS entry_type_name, u.name AS username
                    FROM stock_entries se
                    JOIN products    p  ON p.id  = se.product_id
                    JOIN entry_types et ON et.id = se.entry_type_id
                    JOIN users       u  ON u.id  = se.created_by
                    WHERE se.deleted_at IS NULL
                    ORDER BY se.created_at DESC
                    LIMIT 100
                ");
                $stmt->execute();
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- STOK ÇIKIŞLARI ---
        'stock/outlets' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                $mgr = new \Mudavim\Core\StockManager();
                respond($mgr->recordOutlet($body, $userId), 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- REÇETE DÜŞÜMÜ ---
        'stock/recipe-deduct' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                $mgr = new \Mudavim\Core\StockManager();
                respond($mgr->deductByRecipe(
                    (int) $body['recipe_id'],
                    (float) $body['portions'],
                    (int) $body['location_id'],
                    $userId,
                    $body['table_number'] ?? null
                ), 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- FOOD COST ---
        'report/food-cost' => match ($method) {
            'GET' => (function () use ($id) {
                $mgr = new \Mudavim\Core\FoodCostManager();
                respond($id ? $mgr->calculateRecipeCost($id) : $mgr->getAllRecipeCosts());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- GİDER RAPORU ---
        'report/expenses' => match ($method) {
            'GET' => (function () {
                $s = $_GET['start'] ?? date('Y-m-01');
                $e = $_GET['end']   ?? date('Y-m-d');
                $mgr = new \Mudavim\Core\FoodCostManager();
                respond($mgr->getExpenseReport($s, $e));
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- ALIS LİSTESİ ---
        'stock/shopping-list' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->query("
                    SELECT
                        p.id, p.name, p.sku, p.min_stock_qty,
                        c.name  AS category_name,
                        u.abbreviation AS unit,
                        COALESCE(sb.net_qty, 0) AS current_qty,
                        COALESCE(sb.avg_cost, 0) AS avg_cost,
                        s.id    AS supplier_id,
                        s.name  AS supplier_name,
                        s.phone AS supplier_phone,
                        (
                            SELECT sp2.unit_price
                            FROM supplier_products sp2
                            WHERE sp2.supplier_id = p.default_supplier_id
                              AND sp2.product_id  = p.id
                            ORDER BY sp2.price_date DESC
                            LIMIT 1
                        ) AS last_price
                    FROM products p
                    JOIN categories c  ON c.id = p.category_id
                    JOIN units u       ON u.id = p.stock_unit_id
                    LEFT JOIN stock_balances sb ON sb.product_id = p.id
                    LEFT JOIN suppliers s       ON s.id = p.default_supplier_id AND s.deleted_at IS NULL
                    WHERE p.deleted_at IS NULL
                      AND p.is_tracked = 1
                      AND (
                          COALESCE(sb.net_qty, 0) <= 0
                          OR (p.min_stock_qty > 0 AND COALESCE(sb.net_qty, 0) < p.min_stock_qty)
                      )
                    ORDER BY ISNULL(s.name), s.name, c.name, p.name
                ");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- ÜRÜN AUTOCOMPLETE (UI için) ---
        'products/search' => match ($method) {
            'GET' => (function () {
                $q   = trim($_GET['q'] ?? '');
                $pdo = Database::get();
                $stmt = $pdo->prepare("
                    SELECT p.id, p.name, p.sku, u.abbreviation AS stock_unit,
                           COALESCE(sb.net_qty, 0) AS current_stock,
                           c.name AS category_name
                    FROM products p
                    JOIN units    u  ON u.id  = p.stock_unit_id
                    JOIN categories c ON c.id = p.category_id
                    LEFT JOIN stock_balances sb ON sb.product_id = p.id
                    WHERE p.deleted_at IS NULL
                      AND p.name LIKE :q
                    ORDER BY p.name
                    LIMIT 20
                ");
                $stmt->execute([':q' => "%{$q}%"]);
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- DEMİRBAŞ ENVANTERİ ---
        'assets/list' => match ($method) {
            'GET' => (function () {
                $locationId = (int) ($_GET['location_id'] ?? 0);
                $mgr = new \Mudavim\Core\AssetManager();
                respond($mgr->getAll($locationId));
            })(),
            default => respondError('Method not allowed', 405),
        },

        'assets/loss' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                $required = ['product_id', 'quantity', 'loss_type', 'location_id'];
                foreach ($required as $f) {
                    if (!isset($body[$f])) respondError("{$f} zorunlu", 422);
                }
                $allowed = ['BREAKAGE', 'ASSET_LOST'];
                if (!in_array($body['loss_type'], $allowed)) respondError('Geçersiz loss_type', 422);

                $mgr = new \Mudavim\Core\AssetManager();
                $id  = $mgr->recordLoss(
                    (int)   $body['product_id'],
                    (float) $body['quantity'],
                    (string)$body['loss_type'],
                    (int)   $body['location_id'],
                    $userId,
                    (string)($body['note'] ?? '')
                );
                respond(['outlet_id' => $id, 'recorded' => true], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'assets/add' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                $required = ['product_id', 'quantity', 'location_id'];
                foreach ($required as $f) {
                    if (!isset($body[$f])) respondError("{$f} zorunlu", 422);
                }
                $mgr = new \Mudavim\Core\AssetManager();
                $id  = $mgr->addStock(
                    (int)   $body['product_id'],
                    (float) $body['quantity'],
                    (int)   $body['location_id'],
                    $userId,
                    (float) ($body['unit_price'] ?? 0),
                    (string)($body['note'] ?? '')
                );
                respond(['entry_id' => $id, 'recorded' => true], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'assets/history' => match ($method) {
            'GET' => (function () {
                $months = (int) ($_GET['months'] ?? 1);
                $mgr    = new \Mudavim\Core\AssetManager();
                respond($mgr->getLossHistory(0, $months));
            })(),
            default => respondError('Method not allowed', 405),
        },

        'assets/by-location' => match ($method) {
            'GET' => (function () {
                $mgr = new \Mudavim\Core\AssetManager();
                respond($mgr->getByLocation());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- DAVRANIŞ TAKİBİ (Predictive UI öğrenimi) ---
        'ui/behavior' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                if (empty($body['action_type'])) respondError('action_type zorunlu', 422);
                $pdo = Database::get();
                $hour = (int) ($body['hour'] ?? date('G'));
                $dow  = (int) ($body['dow']  ?? date('w'));
                // Kullanıcının rolünü al
                $roleStmt = $pdo->prepare("SELECT role_id FROM users WHERE id=?");
                $roleStmt->execute([$userId]);
                $roleId   = (int) ($roleStmt->fetchColumn() ?? 1);

                $pdo->prepare("
                    INSERT INTO ui_behavior_patterns
                        (user_id, role_id, hour_of_day, day_of_week, action_type, frequency, last_seen)
                    VALUES (:uid, :rid, :hour, :dow, :act, 1, NOW())
                    ON DUPLICATE KEY UPDATE
                        frequency = frequency + 1,
                        last_seen = NOW()
                ")->execute([
                    ':uid'  => $userId,
                    ':rid'  => $roleId,
                    ':hour' => $hour,
                    ':dow'  => $dow,
                    ':act'  => $body['action_type'],
                ]);
                respond(['tracked' => true]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- ÖNGÖRÜlü UI — Kullanıcı eylem önerileri ---
        'ui/suggestions' => match ($method) {
            'GET' => (function () use ($userId) {
                $pdo  = Database::get();
                $hour = (int) date('G');
                $dow  = (int) date('w');
                $stmt = $pdo->prepare("
                    SELECT action_type, frequency
                    FROM ui_behavior_patterns
                    WHERE user_id = ? AND hour_of_day = ? AND day_of_week = ?
                    ORDER BY frequency DESC
                    LIMIT 5
                ");
                $stmt->execute([$userId, $hour, $dow]);
                $patterns = $stmt->fetchAll();
                // Fallback: ilk kullanım için rol bazlı varsayılan
                if (empty($patterns)) {
                    $roleStmt = $pdo->prepare("SELECT r.code FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?");
                    $roleStmt->execute([$userId]);
                    $role     = $roleStmt->fetchColumn();
                    $patterns = match ($role) {
                        'chef'   => [['action_type' => 'waste_log'],   ['action_type' => 'stock_entry']],
                        'barman' => [['action_type' => 'stock_outlet'], ['action_type' => 'waste_log']],
                        default  => [['action_type' => 'stock_entry'],  ['action_type' => 'stock_outlet']],
                    };
                }
                respond($patterns);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- STOK UYARILARI ---
        'stock/alerts' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->query("
                    SELECT sa.*, p.name AS product_name
                    FROM stock_alerts sa
                    JOIN products p ON p.id = sa.product_id
                    WHERE sa.is_acknowledged = 0
                    ORDER BY FIELD(sa.alert_type,'negative_stock','critical_stock','low_stock','expiry_soon','high_velocity'), sa.created_at DESC
                ");
                respond($stmt->fetchAll());
            })(),
            'POST' => (function () use ($body) {
                // Uyarıyı okundu olarak işaretle
                if (empty($body['id'])) respondError('id zorunlu', 422);
                $pdo = Database::get();
                $pdo->prepare("UPDATE stock_alerts SET is_acknowledged=1, acknowledged_by=1, acknowledged_at=NOW() WHERE id=?")
                    ->execute([$body['id']]);
                respond(['acknowledged' => true]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- STOK BAKİYESİ (tekil ürün) ---
        'stock/balance' => match ($method) {
            'GET' => (function () use ($id) {
                if (!$id) respondError('product_id gerekli', 422);
                $pdo  = Database::get();
                $stmt = $pdo->prepare("
                    SELECT sb.*, p.name, p.min_stock_qty, u.abbreviation AS unit_abbr
                    FROM stock_balances sb
                    JOIN products p ON p.id = sb.product_id
                    JOIN units    u ON u.id = p.stock_unit_id
                    WHERE sb.product_id = ?
                ");
                $stmt->execute([$id]);
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- DASHBOARD ÖZET ---
        'dashboard/summary' => match ($method) {
            'GET' => (function () {
                $pdo   = Database::get();
                $today = date('Y-m-d');

                $entries = $pdo->prepare("SELECT COUNT(*) FROM stock_entries WHERE entry_date=? AND deleted_at IS NULL");
                $entries->execute([$today]);

                $outlets = $pdo->prepare("SELECT COUNT(*) FROM stock_outlets WHERE outlet_date=? AND deleted_at IS NULL");
                $outlets->execute([$today]);

                $waste = $pdo->prepare("
                    SELECT COALESCE(SUM(so.total_cost),0) AS total
                    FROM stock_outlets so JOIN outlet_types ot ON ot.id=so.outlet_type_id
                    WHERE so.outlet_date=? AND ot.accounting_category='waste_loss' AND so.deleted_at IS NULL
                ");
                $waste->execute([$today]);

                $alerts = $pdo->query("SELECT COUNT(*) FROM stock_alerts WHERE is_acknowledged=0");

                $cash = $pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1");
                $cashRow = $cash->fetch();

                respond([
                    'date'           => $today,
                    'entry_count'    => (int) $entries->fetchColumn(),
                    'outlet_count'   => (int) $outlets->fetchColumn(),
                    'waste_value'    => (float) $waste->fetchColumn(),
                    'alert_count'    => (int) $alerts->fetchColumn(),
                    'cash_balance'   => $cashRow ? (float) $cashRow['current_balance'] : null,
                ]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- PIN DOĞRULAMA ---
        'auth/verify-pin' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                if (empty($body['pin'])) respondError('PIN boş olamaz', 422);
                $pdo  = Database::get();
                $stmt = $pdo->prepare("SELECT pin_hash FROM users WHERE id=? AND is_active=1");
                $stmt->execute([$userId]);
                $row = $stmt->fetch();
                if (!$row || !$row['pin_hash']) respondError('PIN tanımlı değil', 403);
                if (!password_verify((string) $body['pin'], $row['pin_hash'])) {
                    respond(['verified' => false, 'message' => 'Yanlış PIN'], 200);
                }
                respond(['verified' => true, 'expires_in' => 1800]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- FİZİKSEL SAYIM: Sayım listesi ---
        'stock/count-list' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->query("
                    SELECT sc.id, sc.count_date, sc.status, sc.notes,
                           u.name AS created_by_name,
                           cu.name AS confirmed_by_name,
                           sc.confirmed_at,
                           (SELECT COUNT(*) FROM stock_count_items WHERE count_id=sc.id) AS item_count,
                           (SELECT COALESCE(SUM(ABS(variance_value)),0) FROM stock_count_items WHERE count_id=sc.id) AS total_variance_value
                    FROM stock_counts sc
                    JOIN users u ON u.id = sc.created_by
                    LEFT JOIN users cu ON cu.id = sc.confirmed_by
                    ORDER BY sc.count_date DESC, sc.id DESC
                    LIMIT 30
                ");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- FİZİKSEL SAYIM: Yeni sayım başlat veya mevcut draft getir ---
        'stock/count-draft' => match ($method) {
            'GET' => (function () use ($userId) {
                $pdo = Database::get();

                // Açık draft/in_progress sayım var mı?
                $existing = $pdo->prepare("
                    SELECT id, count_date, status, notes
                    FROM stock_counts
                    WHERE status IN ('draft','in_progress')
                    ORDER BY id DESC LIMIT 1
                ");
                $existing->execute();
                $draft = $existing->fetch();

                if (!$draft) {
                    // Yeni oluştur
                    $pdo->prepare("
                        INSERT INTO stock_counts (count_date, status, created_by)
                        VALUES (CURDATE(), 'in_progress', ?)
                    ")->execute([$userId]);
                    $countId = (int)$pdo->lastInsertId();
                    $draft = ['id' => $countId, 'count_date' => date('Y-m-d'), 'status' => 'in_progress', 'notes' => null];
                } else {
                    $countId = (int)$draft['id'];
                    // Durumu in_progress'e çek
                    $pdo->prepare("UPDATE stock_counts SET status='in_progress' WHERE id=? AND status='draft'")->execute([$countId]);
                }

                // Tüm takip edilen ürünleri yükle + varsa girilmiş sayım değerleri
                $stmt = $pdo->prepare("
                    SELECT p.id, p.name, p.sku,
                           c.name AS category_name,
                           u.abbreviation AS unit,
                           COALESCE(sb.net_qty, 0)  AS theoretical_qty,
                           COALESCE(sb.avg_cost, 0) AS avg_cost,
                           sci.counted_qty,
                           sci.variance_reason,
                           sci.notes AS item_notes
                    FROM products p
                    JOIN categories c ON c.id = p.category_id
                    JOIN units u      ON u.id = p.stock_unit_id
                    LEFT JOIN stock_balances sb  ON sb.product_id = p.id
                    LEFT JOIN stock_count_items sci ON sci.count_id=? AND sci.product_id=p.id
                    WHERE p.deleted_at IS NULL AND p.is_tracked = 1
                    ORDER BY c.name, p.name
                ");
                $stmt->execute([$countId]);
                $products = $stmt->fetchAll();

                respond(['count' => $draft, 'products' => $products]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- FİZİKSEL SAYIM: Ürün sayım değeri kaydet ---
        'stock/count-item' => match ($method) {
            'POST' => (function () use ($body) {
                $required = ['count_id', 'product_id', 'counted_qty'];
                foreach ($required as $f) {
                    if (!isset($body[$f])) respondError("{$f} zorunlu", 422);
                }
                $pdo = Database::get();

                // Sayımın açık olduğunu doğrula
                $sc = $pdo->prepare("SELECT id FROM stock_counts WHERE id=? AND status='in_progress'");
                $sc->execute([(int)$body['count_id']]);
                if (!$sc->fetch()) respondError('Sayım açık değil', 422);

                // Teorik miktarı ve avg_cost'u al
                $sb = $pdo->prepare("SELECT COALESCE(net_qty,0) AS net_qty, COALESCE(avg_cost,0) AS avg_cost FROM stock_balances WHERE product_id=?");
                $sb->execute([(int)$body['product_id']]);
                $bal = $sb->fetch() ?: ['net_qty' => 0, 'avg_cost' => 0];

                $pdo->prepare("
                    INSERT INTO stock_count_items
                        (count_id, product_id, location_id, theoretical_qty, counted_qty, unit_cost, variance_reason, notes)
                    VALUES (?, ?, 1, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                        counted_qty=VALUES(counted_qty),
                        theoretical_qty=VALUES(theoretical_qty),
                        unit_cost=VALUES(unit_cost),
                        variance_reason=VALUES(variance_reason),
                        notes=VALUES(notes)
                ")->execute([
                    (int)   $body['count_id'],
                    (int)   $body['product_id'],
                    (float) $bal['net_qty'],
                    (float) $body['counted_qty'],
                    (float) $bal['avg_cost'],
                    $body['variance_reason'] ?? 'unknown',
                    $body['notes'] ?? null,
                ]);

                $variance = (float)$body['counted_qty'] - (float)$bal['net_qty'];
                respond([
                    'saved'          => true,
                    'theoretical_qty'=> (float)$bal['net_qty'],
                    'counted_qty'    => (float)$body['counted_qty'],
                    'variance'       => round($variance, 4),
                    'variance_value' => round($variance * (float)$bal['avg_cost'], 2),
                ]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- FİZİKSEL SAYIM: Onayla ve stoğa işle ---
        'stock/count-confirm' => match ($method) {
            'POST' => (function () use ($body, $userId, $userRole) {
                if (empty($body['count_id'])) respondError('count_id zorunlu', 422);
                $countId = (int)$body['count_id'];
                $pdo = Database::get();

                $sc = $pdo->prepare("SELECT id, status FROM stock_counts WHERE id=? AND status='in_progress'");
                $sc->execute([$countId]);
                if (!$sc->fetch()) respondError('Sayım bulunamadı veya zaten onaylandı', 422);

                // Sayım kalemlerini al
                $items = $pdo->prepare("
                    SELECT sci.product_id, sci.counted_qty, sci.theoretical_qty, sci.unit_cost,
                           sci.variance, sci.variance_value
                    FROM stock_count_items sci
                    WHERE sci.count_id = ?
                ");
                $items->execute([$countId]);
                $rows = $items->fetchAll();

                if (!$rows) respondError('Sayımda hiç ürün girilmemiş', 422);

                // COUNT_VARIANCE outlet_type_id
                $otStmt = $pdo->prepare("SELECT id FROM outlet_types WHERE code='COUNT_VARIANCE' LIMIT 1");
                $otStmt->execute();
                $outletTypeId = $otStmt->fetchColumn() ?: null;

                $pdo->beginTransaction();
                try {
                    $applied = 0;
                    foreach ($rows as $row) {
                        $variance = (float)($row['variance'] ?? ((float)$row['counted_qty'] - (float)$row['theoretical_qty']));
                        if (abs($variance) < 0.0001) continue;

                        // stock_balances güncelle
                        $pdo->prepare("
                            INSERT INTO stock_balances (product_id, net_qty, avg_cost, updated_at)
                            VALUES (?, ?, ?, NOW())
                            ON DUPLICATE KEY UPDATE
                                net_qty    = VALUES(net_qty),
                                updated_at = NOW()
                        ")->execute([$row['product_id'], (float)$row['counted_qty'], (float)$row['unit_cost']]);

                        // Negatif fark → sayım açığı olarak outlet kaydet
                        if ($variance < 0 && $outletTypeId) {
                            $pdo->prepare("
                                INSERT INTO stock_outlets
                                    (product_id, outlet_type_id, quantity, unit_cost, outlet_date, notes, created_by)
                                VALUES (?, ?, ?, ?, CURDATE(), 'Sayım açığı', ?)
                            ")->execute([
                                $row['product_id'], $outletTypeId,
                                abs($variance), (float)$row['unit_cost'], $userId,
                            ]);
                        }
                        $applied++;
                    }

                    // Sayımı onayla
                    $pdo->prepare("
                        UPDATE stock_counts
                        SET status='confirmed', confirmed_by=?, confirmed_at=NOW(), notes=?
                        WHERE id=?
                    ")->execute([$userId, $body['notes'] ?? null, $countId]);

                    $pdo->commit();
                    respond(['confirmed' => true, 'items_applied' => $applied]);
                } catch (\Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- STOK DURUMU ---
        'stock/status' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->query("
                    SELECT p.id, p.name, p.sku, p.min_stock_qty, p.is_tracked,
                           c.id AS category_id, c.name AS category_name,
                           u.abbreviation AS unit,
                           COALESCE(sb.net_qty, 0)  AS qty,
                           COALESCE(sb.avg_cost, 0) AS avg_cost,
                           sb.updated_at AS last_movement
                    FROM products p
                    JOIN categories c   ON c.id = p.category_id
                    JOIN units u        ON u.id = p.stock_unit_id
                    LEFT JOIN stock_balances sb ON sb.product_id = p.id
                    WHERE p.deleted_at IS NULL AND p.is_tracked = 1
                    ORDER BY
                        CASE
                            WHEN COALESCE(sb.net_qty,0) <= 0 THEN 0
                            WHEN p.min_stock_qty > 0 AND COALESCE(sb.net_qty,0) < p.min_stock_qty THEN 1
                            ELSE 2
                        END,
                        c.name, p.name
                ");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- KASA ---
        'cash/summary' => match ($method) {
            'GET' => (function () {
                $date = $_GET['date'] ?? date('Y-m-d');
                $pdo  = Database::get();

                $bal  = $pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1")->fetchColumn();
                $txns = $pdo->prepare("
                    SELECT ct.*, u.name AS user_name
                    FROM cash_transactions ct
                    JOIN users u ON u.id = ct.created_by
                    WHERE ct.transaction_date = ? AND ct.deleted_at IS NULL
                    ORDER BY ct.created_at DESC
                ");
                $txns->execute([$date]);

                $zRow = $pdo->prepare("SELECT * FROM z_reports WHERE register_id=1 AND report_date=? LIMIT 1");
                $zRow->execute([$date]);

                respond([
                    'balance'      => $bal !== false ? (float)$bal : 0,
                    'transactions' => $txns->fetchAll(),
                    'z_report'     => $zRow->fetch() ?: null,
                ]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'cash/deposit' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                if (empty($body['amount']) || $body['amount'] <= 0) respondError('Tutar gerekli', 422);
                $pdo    = Database::get();
                $amount = (float) $body['amount'];
                $pdo->prepare("
                    INSERT INTO cash_transactions (register_id,type,amount,description,transaction_date,created_by)
                    VALUES (1,'deposit',?,?,?,?)
                ")->execute([$amount, $body['description'] ?? 'Nakit yatırma', $body['date'] ?? date('Y-m-d'), $userId]);
                $pdo->prepare("UPDATE cash_registers SET current_balance = current_balance + ? WHERE id=1")->execute([$amount]);
                $bal = $pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1")->fetchColumn();
                respond(['balance' => (float)$bal], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'cash/expense' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                if (empty($body['amount']) || $body['amount'] <= 0) respondError('Tutar gerekli', 422);
                if (empty($body['pin'])) respondError('PIN zorunlu', 422);
                // PIN doğrula
                $pdo     = Database::get();
                $pinHash = $pdo->prepare("SELECT pin_hash FROM users WHERE id=? LIMIT 1");
                $pinHash->execute([$userId]);
                $hash = $pinHash->fetchColumn();
                if (!$hash || !password_verify($body['pin'], $hash)) respondError('PIN hatalı', 403);

                $amount = -(float) $body['amount']; // negatif = çıkış
                $pdo->prepare("
                    INSERT INTO cash_transactions (register_id,type,amount,description,transaction_date,created_by,pin_verified)
                    VALUES (1,'cash_expense',?,?,?,?,1)
                ")->execute([$amount, $body['description'] ?? 'Masraf', $body['date'] ?? date('Y-m-d'), $userId]);
                $pdo->prepare("UPDATE cash_registers SET current_balance = current_balance + ? WHERE id=1")->execute([$amount]);
                $bal = $pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1")->fetchColumn();
                respond(['balance' => (float)$bal], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'cash/adjust' => match ($method) {
            'POST' => (function () use ($body, $userId, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (!isset($body['new_balance'])) respondError('Yeni bakiye gerekli', 422);
                $pdo     = Database::get();
                $current = (float)$pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1")->fetchColumn();
                $target  = (float)$body['new_balance'];
                $diff    = round($target - $current, 2);
                $pdo->prepare("
                    INSERT INTO cash_transactions (register_id,type,amount,description,transaction_date,created_by)
                    VALUES (1,'manual_adjust',?,?,?,?)
                ")->execute([$diff, $body['description'] ?? 'Manuel düzeltme', date('Y-m-d'), $userId]);
                $pdo->prepare("UPDATE cash_registers SET current_balance = ? WHERE id=1")->execute([$target]);
                respond(['balance' => $target, 'diff' => $diff]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'cash/z-report' => match ($method) {
            'POST' => (function () use ($body, $userId, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                $date = $body['date'] ?? date('Y-m-d');
                $pdo  = Database::get();

                // Bugünün işlem özeti
                $row = $pdo->prepare("
                    SELECT
                        COALESCE(SUM(CASE WHEN amount > 0 AND type='deposit' THEN amount ELSE 0 END),0) AS sales,
                        COALESCE(SUM(CASE WHEN amount < 0 AND type NOT IN ('patron_withdraw') THEN ABS(amount) ELSE 0 END),0) AS expenses,
                        COALESCE(SUM(CASE WHEN type='patron_withdraw' THEN ABS(amount) ELSE 0 END),0) AS withdrawals
                    FROM cash_transactions WHERE transaction_date=? AND deleted_at IS NULL AND register_id=1
                ");
                $row->execute([$date]);
                $agg = $row->fetch();

                $closing = (float)$pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1")->fetchColumn();
                // Opening = closing - deposits + expenses + withdrawals (approximate)
                $opening = round($closing - ($agg['sales'] ?? 0) + ($agg['expenses'] ?? 0) + ($agg['withdrawals'] ?? 0), 2);

                $pdo->prepare("
                    INSERT INTO z_reports
                        (register_id,report_date,opening_balance,closing_balance,total_sales,total_expenses,total_withdrawals,notes,created_by)
                    VALUES (1,?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                        closing_balance=VALUES(closing_balance), total_sales=VALUES(total_sales),
                        total_expenses=VALUES(total_expenses), total_withdrawals=VALUES(total_withdrawals),
                        notes=VALUES(notes), created_by=VALUES(created_by)
                ")->execute([$date, $opening, $closing, $agg['sales'], $agg['expenses'], $agg['withdrawals'],
                             $body['notes'] ?? null, $userId]);

                respond(['closing_balance' => $closing, 'report_date' => $date]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- RAPORLAR ---
        'report/summary' => match ($method) {
            'GET' => (function () {
                $start = $_GET['start'] ?? date('Y-m-01');
                $end   = $_GET['end']   ?? date('Y-m-d');
                $pdo   = Database::get();

                $eRow = $pdo->prepare("
                    SELECT COUNT(*) AS cnt, COALESCE(SUM(total_price),0) AS total
                    FROM stock_entries WHERE entry_date BETWEEN ? AND ? AND deleted_at IS NULL
                ");
                $eRow->execute([$start, $end]);
                $e = $eRow->fetch();

                $oRow = $pdo->prepare("
                    SELECT COUNT(*) AS cnt,
                           COALESCE(SUM(so.total_cost),0) AS total,
                           COALESCE(SUM(CASE WHEN ot.accounting_category='waste_loss' THEN so.total_cost ELSE 0 END),0) AS waste
                    FROM stock_outlets so
                    JOIN outlet_types ot ON ot.id=so.outlet_type_id
                    WHERE so.outlet_date BETWEEN ? AND ? AND so.deleted_at IS NULL
                ");
                $oRow->execute([$start, $end]);
                $o = $oRow->fetch();

                $cash = $pdo->query("SELECT current_balance FROM cash_registers WHERE id=1 LIMIT 1")->fetchColumn();

                respond([
                    'entry_count'  => (int)   $e['cnt'],
                    'entry_total'  => (float)  $e['total'],
                    'outlet_count' => (int)   $o['cnt'],
                    'outlet_total' => (float)  $o['total'],
                    'waste_value'  => (float)  $o['waste'],
                    'cash_balance' => $cash !== false ? (float)$cash : null,
                ]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'report/movements' => match ($method) {
            'GET' => (function () {
                $start  = $_GET['start'] ?? date('Y-m-01');
                $end    = $_GET['end']   ?? date('Y-m-d');
                $limit  = min((int)($_GET['limit'] ?? 200), 500);
                $pdo    = Database::get();

                $e = $pdo->prepare("
                    SELECT 'entry' AS kind, se.id,
                           et.name AS type_name, et.code AS type_code,
                           p.name AS product_name, c.name AS category_name,
                           se.quantity_net AS qty, u.abbreviation AS unit,
                           se.total_price AS total_cost, se.entry_date AS txn_date, se.created_at,
                           us.name AS user_name
                    FROM stock_entries se
                    JOIN entry_types et ON et.id=se.entry_type_id
                    JOIN products p     ON p.id=se.product_id
                    JOIN categories c   ON c.id=p.category_id
                    JOIN units u        ON u.id=se.unit_id
                    JOIN users us       ON us.id=se.created_by
                    WHERE se.entry_date BETWEEN ? AND ? AND se.deleted_at IS NULL
                ");
                $e->execute([$start, $end]);

                $o = $pdo->prepare("
                    SELECT 'outlet' AS kind, so.id,
                           ot.name AS type_name, ot.code AS type_code,
                           p.name AS product_name, c.name AS category_name,
                           so.quantity AS qty, u.abbreviation AS unit,
                           so.total_cost, so.outlet_date AS txn_date, so.created_at,
                           us.name AS user_name, ot.accounting_category
                    FROM stock_outlets so
                    JOIN outlet_types ot ON ot.id=so.outlet_type_id
                    JOIN products p      ON p.id=so.product_id
                    JOIN categories c    ON c.id=p.category_id
                    JOIN units u         ON u.id=so.unit_id
                    JOIN users us        ON us.id=so.created_by
                    WHERE so.outlet_date BETWEEN ? AND ? AND so.deleted_at IS NULL
                ");
                $o->execute([$start, $end]);

                $rows = array_merge($e->fetchAll(), $o->fetchAll());
                usort($rows, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
                respond(array_slice($rows, 0, $limit));
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- HIZLI GEÇMİŞ (son 20 işlem) ---
        'dashboard/recent' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $today = date('Y-m-d');
                // Girişler
                $e = $pdo->prepare("
                    SELECT 'entry' AS kind, se.id, et.name AS type_name, p.name AS product_name,
                           se.quantity_net AS qty, u.abbreviation AS unit, se.created_at,
                           us.name AS user_name
                    FROM stock_entries se
                    JOIN entry_types et ON et.id=se.entry_type_id
                    JOIN products p     ON p.id=se.product_id
                    JOIN units u        ON u.id=se.unit_id
                    JOIN users us       ON us.id=se.created_by
                    WHERE se.entry_date=? AND se.deleted_at IS NULL
                ");
                $e->execute([$today]);
                // Çıkışlar
                $o = $pdo->prepare("
                    SELECT 'outlet' AS kind, so.id, ot.name AS type_name, p.name AS product_name,
                           so.quantity AS qty, u.abbreviation AS unit, so.created_at,
                           us.name AS user_name
                    FROM stock_outlets so
                    JOIN outlet_types ot ON ot.id=so.outlet_type_id
                    JOIN products p      ON p.id=so.product_id
                    JOIN units u         ON u.id=so.unit_id
                    JOIN users us        ON us.id=so.created_by
                    WHERE so.outlet_date=? AND so.deleted_at IS NULL
                ");
                $o->execute([$today]);

                $rows = array_merge($e->fetchAll(), $o->fetchAll());
                usort($rows, fn($a,$b) => strcmp($b['created_at'], $a['created_at']));
                respond(array_slice($rows, 0, 20));
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- PERSONEL YÖNETİMİ (sadece patron) ---
        'staff/list' => match ($method) {
            'GET' => (function () use ($userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                $pdo  = Database::get();
                $stmt = $pdo->query("
                    SELECT u.id, u.name, u.username, u.is_active, u.last_login_at, u.created_at,
                           r.code AS role_code, r.name AS role_name
                    FROM users u
                    JOIN roles r ON r.id = u.role_id
                    WHERE u.deleted_at IS NULL
                    ORDER BY u.is_active DESC, r.id, u.name
                ");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        'staff/create' => match ($method) {
            'POST' => (function () use ($body, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (empty($body['name']))     respondError('Ad Soyad zorunlu', 422);
                if (empty($body['username'])) respondError('Kullanıcı adı zorunlu', 422);
                if (empty($body['password']) || strlen($body['password']) < 6)
                    respondError('Şifre en az 6 karakter olmalı', 422);
                if (empty($body['role_code'])) respondError('Rol zorunlu', 422);

                $pdo     = Database::get();
                $roleRow = $pdo->prepare("SELECT id FROM roles WHERE code=? LIMIT 1");
                $roleRow->execute([$body['role_code']]);
                $roleId  = $roleRow->fetchColumn();
                if (!$roleId) respondError('Geçersiz rol', 422);

                $pinHash = !empty($body['pin']) && preg_match('/^\d{4}$/', $body['pin'])
                           ? password_hash($body['pin'], PASSWORD_DEFAULT) : null;

                $stmt = $pdo->prepare("
                    INSERT INTO users (role_id, name, username, password_hash, pin_hash, is_active)
                    VALUES (:role, :name, :uname, :pass, :pin, 1)
                ");
                $stmt->execute([
                    ':role'  => $roleId,
                    ':name'  => trim($body['name']),
                    ':uname' => trim($body['username']),
                    ':pass'  => password_hash($body['password'], PASSWORD_DEFAULT),
                    ':pin'   => $pinHash,
                ]);
                respond(['id' => (int) $pdo->lastInsertId()], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'staff/update' => match ($method) {
            'PUT' => (function () use ($body, $id, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (!$id) respondError('ID gerekli', 422);
                if (empty($body['name']))     respondError('Ad Soyad zorunlu', 422);
                if (empty($body['username'])) respondError('Kullanıcı adı zorunlu', 422);
                if (empty($body['role_code'])) respondError('Rol zorunlu', 422);

                $pdo     = Database::get();
                $roleRow = $pdo->prepare("SELECT id FROM roles WHERE code=? LIMIT 1");
                $roleRow->execute([$body['role_code']]);
                $roleId  = $roleRow->fetchColumn();
                if (!$roleId) respondError('Geçersiz rol', 422);

                $isActive = isset($body['is_active']) ? (int)(bool)$body['is_active'] : 1;

                $pdo->prepare("
                    UPDATE users SET role_id=:role, name=:name, username=:uname, is_active=:active
                    WHERE id=:id AND deleted_at IS NULL
                ")->execute([':role'=>$roleId,':name'=>trim($body['name']),
                             ':uname'=>trim($body['username']),':active'=>$isActive,':id'=>$id]);

                // Şifre değişikliği (isteğe bağlı)
                if (!empty($body['password'])) {
                    if (strlen($body['password']) < 6) respondError('Şifre en az 6 karakter', 422);
                    $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")
                        ->execute([password_hash($body['password'], PASSWORD_DEFAULT), $id]);
                }

                // PIN değişikliği (isteğe bağlı)
                if (isset($body['pin']) && $body['pin'] !== '') {
                    if (!preg_match('/^\d{4}$/', $body['pin'])) respondError('PIN 4 rakam olmalı', 422);
                    $pdo->prepare("UPDATE users SET pin_hash=? WHERE id=?")
                        ->execute([password_hash($body['pin'], PASSWORD_DEFAULT), $id]);
                }

                respond(['updated' => true]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // Personel finans: aylık özet + maaş + avanslar
        'staff/finance' => match ($method) {
            'GET' => (function () use ($userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                $uid   = (int) ($_GET['user_id'] ?? 0);
                $month = $_GET['month'] ?? date('Y-m'); // '2026-06'
                if (!$uid) respondError('user_id gerekli', 422);
                $pdo   = Database::get();

                // Geçerli maaş (en son valid_from ≤ bu ay)
                $sal = $pdo->prepare("
                    SELECT * FROM staff_salaries
                    WHERE user_id=? AND valid_from <= ?
                    ORDER BY valid_from DESC LIMIT 1
                ");
                $sal->execute([$uid, $month . '-01']);
                $salary = $sal->fetch() ?: null;

                // Bu aydaki avanslar
                $adv = $pdo->prepare("
                    SELECT sa.*, u.name AS creator_name
                    FROM staff_advances sa
                    JOIN users u ON u.id = sa.created_by
                    WHERE sa.user_id=? AND DATE_FORMAT(sa.given_date,'%Y-%m')=?
                    ORDER BY sa.given_date
                ");
                $adv->execute([$uid, $month]);
                $advances = $adv->fetchAll();

                // Bu ay ödeme yapıldı mı?
                $pay = $pdo->prepare("SELECT * FROM staff_payments WHERE user_id=? AND period_month=? LIMIT 1");
                $pay->execute([$uid, $month]);
                $payment = $pay->fetch() ?: null;

                // Son 6 ay ödeme geçmişi
                $hist = $pdo->prepare("
                    SELECT sp.*, u.name AS creator_name
                    FROM staff_payments sp
                    JOIN users u ON u.id = sp.created_by
                    WHERE sp.user_id=?
                    ORDER BY sp.period_month DESC LIMIT 6
                ");
                $hist->execute([$uid]);

                respond([
                    'salary'        => $salary,
                    'advances'      => $advances,
                    'advance_total' => array_sum(array_column($advances, 'amount')),
                    'payment'       => $payment,
                    'history'       => $hist->fetchAll(),
                ]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // Maaş tanımla/güncelle
        'staff/salary' => match ($method) {
            'POST' => (function () use ($body, $userId, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (empty($body['user_id'])) respondError('user_id gerekli', 422);
                if (!isset($body['amount']) || $body['amount'] < 0) respondError('Tutar gerekli', 422);
                $month = $body['month'] ?? date('Y-m');
                $pdo   = Database::get();
                $pdo->prepare("
                    INSERT INTO staff_salaries (user_id, amount, valid_from, note, created_by)
                    VALUES (?,?,?,?,?)
                ")->execute([(int)$body['user_id'], (float)$body['amount'],
                             $month.'-01', $body['note']??null, $userId]);
                respond(['ok' => true], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // Avans ekle
        'staff/advance' => match ($method) {
            'POST' => (function () use ($body, $userId, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (empty($body['user_id']))  respondError('user_id gerekli', 422);
                if (empty($body['amount']) || $body['amount'] <= 0) respondError('Tutar gerekli', 422);
                $pdo = Database::get();
                $pdo->prepare("
                    INSERT INTO staff_advances (user_id, amount, given_date, note, created_by)
                    VALUES (?,?,?,?,?)
                ")->execute([(int)$body['user_id'], (float)$body['amount'],
                             $body['given_date'] ?? date('Y-m-d'),
                             $body['note'] ?? null, $userId]);
                respond(['id' => (int)$pdo->lastInsertId()], 201);
            })(),
            'DELETE' => (function () use ($id, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (!$id) respondError('ID gerekli', 422);
                $pdo = Database::get();
                $pdo->prepare("DELETE FROM staff_advances WHERE id=?")->execute([$id]);
                respond(['deleted' => true]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // Ay sonu ödeme kaydet
        'staff/payment' => match ($method) {
            'POST' => (function () use ($body, $userId, $userRole) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (empty($body['user_id'])) respondError('user_id gerekli', 422);
                $month    = $body['month'] ?? date('Y-m');
                $gross    = (float)($body['gross_salary']   ?? 0);
                $advances = (float)($body['total_advances'] ?? 0);
                $net      = round($gross - $advances, 2);
                $pdo      = Database::get();
                $pdo->prepare("
                    INSERT INTO staff_payments
                        (user_id, period_month, gross_salary, total_advances, net_payment, paid_date, note, created_by)
                    VALUES (?,?,?,?,?,?,?,?)
                    ON DUPLICATE KEY UPDATE
                        gross_salary=VALUES(gross_salary), total_advances=VALUES(total_advances),
                        net_payment=VALUES(net_payment), paid_date=VALUES(paid_date),
                        note=VALUES(note), created_by=VALUES(created_by)
                ")->execute([(int)$body['user_id'], $month, $gross, $advances, $net,
                              $body['paid_date'] ?? date('Y-m-d'),
                              $body['note'] ?? null, $userId]);
                respond(['net_payment' => $net]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'staff/deactivate' => match ($method) {
            'PUT' => (function () use ($body, $id, $userRole, $userId) {
                if ($userRole !== 'patron') respondError('Yetki yok', 403);
                if (!$id) respondError('ID gerekli', 422);
                if ($id === $userId) respondError('Kendinizi devre dışı bırakamazsınız', 422);
                $active = isset($body['is_active']) ? (int)(bool)$body['is_active'] : 0;
                $pdo = Database::get();
                $pdo->prepare("UPDATE users SET is_active=? WHERE id=? AND deleted_at IS NULL")
                    ->execute([$active, $id]);
                respond(['is_active' => $active]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- KATEGORİLER ---
        'categories/list' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $type = $_GET['type'] ?? null;
                if ($type) {
                    $stmt = $pdo->prepare("SELECT id, name, product_type FROM categories WHERE deleted_at IS NULL AND product_type=? ORDER BY name");
                    $stmt->execute([$type]);
                } else {
                    $stmt = $pdo->query("SELECT id, name, product_type FROM categories WHERE deleted_at IS NULL ORDER BY name");
                }
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- BİRİMLER ---
        'units/list' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->query("SELECT id, name, abbreviation, unit_type FROM units ORDER BY unit_type, name");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- ÜRÜN YÖNETİMİ ---
        'products/list' => match ($method) {
            'GET' => (function () {
                $pdo = Database::get();
                $stmt = $pdo->query("
                    SELECT p.id, p.name, p.sku, p.is_tracked, p.min_stock_qty,
                           p.count_frequency, p.yield_percentage,
                           c.id AS category_id, c.name AS category_name,
                           u.id AS stock_unit_id, u.abbreviation AS stock_unit,
                           pu.id AS purchase_unit_id,
                           COALESCE(sb.net_qty, 0) AS current_stock
                    FROM products p
                    JOIN categories c  ON c.id  = p.category_id
                    JOIN units u       ON u.id  = p.stock_unit_id
                    JOIN units pu      ON pu.id = p.purchase_unit_id
                    LEFT JOIN stock_balances sb ON sb.product_id = p.id
                    WHERE p.deleted_at IS NULL
                    ORDER BY c.name, p.name
                ");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        'products/create' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                if (empty($body['name']))        respondError('Ürün adı zorunlu', 422);
                if (empty($body['category_id'])) respondError('Kategori zorunlu', 422);
                if (empty($body['unit_id']))      respondError('Birim zorunlu', 422);

                $pdo       = Database::get();
                $name      = trim($body['name']);
                $sku       = !empty($body['sku']) ? trim($body['sku']) : null;
                $catId     = (int)   $body['category_id'];
                $unitId    = (int)   $body['unit_id'];
                $minQty    = (float) ($body['min_stock_qty'] ?? 0);
                $tracked   = isset($body['is_tracked']) ? (int)(bool)$body['is_tracked'] : 1;
                $freq      = in_array($body['count_frequency'] ?? '', ['daily','weekly','monthly','never'])
                             ? $body['count_frequency'] : 'weekly';

                $stmt = $pdo->prepare("
                    INSERT INTO products
                        (category_id, name, sku, purchase_unit_id, stock_unit_id, recipe_unit_id,
                         min_stock_qty, is_tracked, count_frequency, created_by)
                    VALUES (:cat,:name,:sku,:unit,:unit,:unit,:minqty,:tracked,:freq,:user)
                ");
                $stmt->execute([
                    ':cat'     => $catId,  ':name'    => $name,    ':sku'  => $sku,
                    ':unit'    => $unitId, ':minqty'  => $minQty,
                    ':tracked' => $tracked, ':freq'   => $freq,    ':user' => $userId,
                ]);
                respond(['id' => (int) $pdo->lastInsertId()], 201);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'products/update' => match ($method) {
            'PUT' => (function () use ($body, $id) {
                if (!$id)                        respondError('ID gerekli', 422);
                if (empty($body['name']))        respondError('Ürün adı zorunlu', 422);
                if (empty($body['category_id'])) respondError('Kategori zorunlu', 422);
                if (empty($body['unit_id']))      respondError('Birim zorunlu', 422);

                $pdo     = Database::get();
                $name    = trim($body['name']);
                $sku     = !empty($body['sku']) ? trim($body['sku']) : null;
                $catId   = (int)   $body['category_id'];
                $unitId  = (int)   $body['unit_id'];
                $minQty  = (float) ($body['min_stock_qty'] ?? 0);
                $tracked = isset($body['is_tracked']) ? (int)(bool)$body['is_tracked'] : 1;
                $freq    = in_array($body['count_frequency'] ?? '', ['daily','weekly','monthly','never'])
                           ? $body['count_frequency'] : 'weekly';

                $stmt = $pdo->prepare("
                    UPDATE products SET
                        name=:name, sku=:sku, category_id=:cat,
                        purchase_unit_id=:unit, stock_unit_id=:unit, recipe_unit_id=:unit,
                        min_stock_qty=:minqty, is_tracked=:tracked, count_frequency=:freq
                    WHERE id=:id AND deleted_at IS NULL
                ");
                $stmt->execute([
                    ':name'    => $name,    ':sku'     => $sku,     ':cat'     => $catId,
                    ':unit'    => $unitId,  ':minqty'  => $minQty,
                    ':tracked' => $tracked, ':freq'    => $freq,    ':id'      => $id,
                ]);
                respond(['updated' => $stmt->rowCount() > 0]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'products/delete' => match ($method) {
            'DELETE' => (function () use ($id) {
                if (!$id) respondError('ID gerekli', 422);
                $pdo  = Database::get();
                $stmt = $pdo->prepare("UPDATE products SET deleted_at=NOW() WHERE id=? AND deleted_at IS NULL");
                $stmt->execute([$id]);
                respond(['deleted' => $stmt->rowCount() > 0]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        // --- TEDARİKÇİ ---
        'supplier/list' => match ($method) {
            'GET' => (function () {
                $pdo  = Database::get();
                $stmt = $pdo->query("
                    SELECT s.id, s.name, s.contact_name, s.phone, s.tax_number,
                           s.payment_terms, s.notes, s.is_active, s.cari_id,
                           COALESCE(ca.balance, 0) AS balance
                    FROM suppliers s
                    LEFT JOIN cari_accounts ca ON ca.id = s.cari_id
                    WHERE s.deleted_at IS NULL
                    ORDER BY s.name
                ");
                respond($stmt->fetchAll());
            })(),
            default => respondError('Method not allowed', 405),
        },

        'supplier/create' => match ($method) {
            'POST' => (function () use ($body, $userId) {
                if (empty($body['name'])) respondError('İsim zorunlu', 422);
                $pdo = Database::get();
                $pdo->beginTransaction();
                try {
                    // Cari hesap oluştur
                    $pdo->prepare("
                        INSERT INTO cari_accounts (type, name, phone, is_active)
                        VALUES ('supplier', ?, ?, 1)
                    ")->execute([trim($body['name']), $body['phone'] ?? null]);
                    $cariId = (int) $pdo->lastInsertId();

                    // Tedarikçi ekle
                    $pdo->prepare("
                        INSERT INTO suppliers (name, contact_name, phone, tax_number, payment_terms, notes, cari_id)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ")->execute([
                        trim($body['name']),
                        $body['contact_name'] ?? null,
                        $body['phone'] ?? null,
                        $body['tax_number'] ?? null,
                        (int) ($body['payment_terms'] ?? 0),
                        $body['notes'] ?? null,
                        $cariId,
                    ]);
                    $id = (int) $pdo->lastInsertId();
                    $pdo->commit();
                    respond(['id' => $id, 'cari_id' => $cariId], 201);
                } catch (\Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            })(),
            default => respondError('Method not allowed', 405),
        },

        'supplier/update' => match ($method) {
            'PUT' => (function () use ($body, $id) {
                if (!$id) respondError('ID gerekli', 422);
                if (empty($body['name'])) respondError('İsim zorunlu', 422);
                $pdo = Database::get();
                $pdo->prepare("
                    UPDATE suppliers SET name=?, contact_name=?, phone=?, tax_number=?, payment_terms=?, notes=?, is_active=?
                    WHERE id=? AND deleted_at IS NULL
                ")->execute([
                    trim($body['name']),
                    $body['contact_name'] ?? null,
                    $body['phone'] ?? null,
                    $body['tax_number'] ?? null,
                    (int) ($body['payment_terms'] ?? 0),
                    $body['notes'] ?? null,
                    isset($body['is_active']) ? (int) $body['is_active'] : 1,
                    $id,
                ]);
                // Cari hesap adını da güncelle
                $row = $pdo->prepare("SELECT cari_id FROM suppliers WHERE id=?");
                $row->execute([$id]);
                $cid = $row->fetchColumn();
                if ($cid) {
                    $pdo->prepare("UPDATE cari_accounts SET name=?, phone=? WHERE id=?")
                        ->execute([trim($body['name']), $body['phone'] ?? null, $cid]);
                }
                respond(['updated' => true]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'supplier/delete' => match ($method) {
            'DELETE' => (function () use ($id) {
                if (!$id) respondError('ID gerekli', 422);
                $pdo = Database::get();
                $pdo->prepare("UPDATE suppliers SET deleted_at=NOW(), is_active=0 WHERE id=? AND deleted_at IS NULL")
                    ->execute([$id]);
                respond(['deleted' => true]);
            })(),
            default => respondError('Method not allowed', 405),
        },

        'supplier/cari' => match ($method) {
            'GET' => (function () use ($id) {
                // id = supplier_id
                if (!$id) respondError('ID gerekli', 422);
                $pdo = Database::get();
                $row = $pdo->prepare("SELECT cari_id, name FROM suppliers WHERE id=? AND deleted_at IS NULL");
                $row->execute([$id]);
                $sup = $row->fetch();
                if (!$sup) respondError('Tedarikçi bulunamadı', 404);

                $cariId = $sup['cari_id'];
                $balance = 0;
                $txns    = [];

                if ($cariId) {
                    $b = $pdo->prepare("SELECT balance FROM cari_accounts WHERE id=?");
                    $b->execute([$cariId]);
                    $balance = (float) ($b->fetchColumn() ?: 0);

                    $t = $pdo->prepare("
                        SELECT ct.id, ct.amount, ct.description, ct.transaction_date, ct.reference_type,
                               u.name AS created_by_name
                        FROM cari_transactions ct
                        JOIN users u ON u.id = ct.created_by
                        WHERE ct.cari_id = ?
                        ORDER BY ct.transaction_date DESC, ct.id DESC
                        LIMIT 100
                    ");
                    $t->execute([$cariId]);
                    $txns = $t->fetchAll();
                }

                respond([
                    'supplier_name' => $sup['name'],
                    'cari_id'       => $cariId,
                    'balance'       => $balance,
                    'transactions'  => $txns,
                ]);
            })(),
            'POST' => (function () use ($id, $body, $userId) {
                // id = supplier_id, body: type ('borc'|'odeme'), amount, description, date
                if (!$id) respondError('ID gerekli', 422);
                if (empty($body['amount']) || (float)$body['amount'] <= 0) respondError('Tutar gerekli', 422);
                if (empty($body['type'])) respondError('Tür gerekli', 422);

                $pdo = Database::get();
                $row = $pdo->prepare("SELECT cari_id FROM suppliers WHERE id=? AND deleted_at IS NULL");
                $row->execute([$id]);
                $cariId = $row->fetchColumn();
                if (!$cariId) respondError('Cari hesap bulunamadı', 404);

                // borc: restoran borçlandı (pozitif), odeme: biz ödedik (negatif)
                $amount = $body['type'] === 'borc'
                    ? abs((float)$body['amount'])
                    : -abs((float)$body['amount']);

                $date = $body['date'] ?? date('Y-m-d');
                $desc = $body['description'] ?? ($body['type'] === 'borc' ? 'Borç' : 'Ödeme');

                $pdo->beginTransaction();
                try {
                    $pdo->prepare("
                        INSERT INTO cari_transactions (cari_id, amount, description, transaction_date, created_by)
                        VALUES (?, ?, ?, ?, ?)
                    ")->execute([$cariId, $amount, $desc, $date, $userId]);

                    $pdo->prepare("UPDATE cari_accounts SET balance = balance + ? WHERE id=?")
                        ->execute([$amount, $cariId]);

                    $pdo->commit();
                    $bs = $pdo->prepare("SELECT balance FROM cari_accounts WHERE id=?");
                    $bs->execute([$cariId]);
                    respond(['balance' => (float)$bs->fetchColumn()], 201);
                } catch (\Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            })(),
            default => respondError('Method not allowed', 405),
        },

        'supplier/cari-delete' => match ($method) {
            'DELETE' => (function () use ($id, $userId) {
                // id = cari_transaction id
                if (!$id) respondError('ID gerekli', 422);
                $pdo = Database::get();
                // Tutarı al, bakiyeden geri al
                $t = $pdo->prepare("SELECT cari_id, amount FROM cari_transactions WHERE id=?");
                $t->execute([$id]);
                $txn = $t->fetch();
                if (!$txn) respondError('Kayıt bulunamadı', 404);

                $pdo->beginTransaction();
                try {
                    $pdo->prepare("DELETE FROM cari_transactions WHERE id=?")->execute([$id]);
                    $pdo->prepare("UPDATE cari_accounts SET balance = balance - ? WHERE id=?")
                        ->execute([$txn['amount'], $txn['cari_id']]);
                    $pdo->commit();
                    $bs = $pdo->prepare("SELECT balance FROM cari_accounts WHERE id=?");
                    $bs->execute([$txn['cari_id']]);
                    respond(['balance' => (float)$bs->fetchColumn()]);
                } catch (\Throwable $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            })(),
            default => respondError('Method not allowed', 405),
        },

        default => respondError("Endpoint bulunamadı: {$module}/{$entity}", 404),
    };

} catch (\InvalidArgumentException $e) {
    respondError($e->getMessage(), 422);
} catch (\RuntimeException $e) {
    respondError($e->getMessage(), 400);
} catch (\Throwable $e) {
    error_log("[Mudavim] " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    respondError('Sunucu hatası', 500);
}
