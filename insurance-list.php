<?php
/**
 * Insurance management page (for the currently logged-in user).
 *
 * Allows a user to add/remove insurance products from their active_insurances list.
 * Data is stored in Database.csv:
 * - active_insurances : comma-separated insurance codes (e.g., "nemovitost,zivotni")
 * - MT               : total monthly price for all active insurances (CZK)
 *
 * This page:
 * - Loads the current user row from Database.csv using $_SESSION['user_id']
 * - Renders two lists:
 *     - Available products (not currently active)
 *     - Active products (with remove buttons)
 * - Uses POST forms to update the CSV and then redirects (POST-Redirect-GET pattern).
 *
 * Security:
 * - Requires authentication (auth.php).
 * - Uses htmlspecialchars() for all user-controlled output to prevent XSS.
 */
require_once __DIR__ . '/auth.php';

$csvPath = __DIR__ . '/Database.csv';
$myId = (string)$_SESSION['user_id'];

$products = array(
    array("code" => "nemovitost", "label" => "Nemovitostní",    "price" => 250),
    array("code" => "zivotni",    "label" => "Životní",          "price" => 199),
    array("code" => "zdravotni",  "label" => "Zdravotní",        "price" => 149),
    array("code" => "povinne",    "label" => "Povinné ručení",   "price" => 320),
    array("code" => "zvirata",    "label" => "Pojištění zvířat", "price" => 180),
);
/**
 * HTML-escapes text for safe output in HTML contexts.
 *
 * @param mixed $s Input value.
 * @return string Escaped UTF-8 string.
 */
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/**
 * Loads a single user row from a semicolon-delimited CSV file by id.
 *
 * @param string $path Absolute path to Database.csv.
 * @param string $id   User id to search for.
 * @return array|null  Associative user row (header => value) or null if not found.
 */
function loadUserById($path, $id) {
    if (!file_exists($path)) return null;
    $fh = fopen($path, "r");
    if (!$fh) return null;

    $headers = fgetcsv($fh, 0, ";");
    if (!$headers) { fclose($fh); return null; }
    $col = array_flip($headers);

    while (($row = fgetcsv($fh, 0, ";")) !== false) {
        if (count($row) !== count($headers)) continue;
        if ((string)$row[$col["id"]] === (string)$id) {
            fclose($fh);
            return array_combine($headers, $row);
        }
    }
    fclose($fh);
    return null;
}

$user = loadUserById($csvPath, $myId);
if (!$user) { http_response_code(404); exit("User not found"); }

// Parse active_insurances (codes)
$raw = isset($user['active_insurances']) ? trim($user['active_insurances']) : '';
$activeSet = array();
if ($raw !== '') {
    foreach (explode(',', $raw) as $c) {
        $c = trim($c);
        if ($c !== '') $activeSet[$c] = true;
    }
}

// Build lookup for products
$productByCode = array();
foreach ($products as $p) $productByCode[$p['code']] = $p;

// Split lists + compute total
$available = array();
$active = array();
$totalMonthly = 0;

foreach ($products as $p) {
    if (isset($activeSet[$p['code']])) {
        $active[] = $p;
        $totalMonthly += (int)$p['price'];
    } else {
        $available[] = $p;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Správa pojištění</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php require_once __DIR__ . '/header.php'; ?>

<div class="insurance-main-wrapper">
    <div>
        <h3>Dostupná pojištění</h3>
        <ul>
            <?php foreach ($available as $p): ?>
                <li>
                    <?php echo h($p['label']); ?> (<?php echo h($p['price']); ?> Kč / měs.)
                    <form method="POST" action="update-insurance.php" style="display:inline;">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="code" value="<?php echo h($p['code']); ?>">
                        <button class="button-20" type="submit">Přidat</button>
                    </form>
                </li>
            <?php endforeach; ?>
            <?php if (count($available) === 0): ?>
                <li>Žádné další pojištění není k dispozici.</li>
            <?php endif; ?>
        </ul>

        <div class="table-line-divider"></div>

        <h3>Vaše aktivní pojištění</h3>
        <div style="margin-bottom:10px;">
            Celkem měsíčně (MT): <b><?php echo h($totalMonthly); ?> Kč</b>
        </div>

        <ul>
            <?php foreach ($active as $p): ?>
                <li>
                    <?php echo h($p['label']); ?> (<?php echo h($p['price']); ?> Kč / měs.)
                    <form method="POST" action="update-insurance.php" style="display:inline;">
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="code" value="<?php echo h($p['code']); ?>">
                        <button class="button-20" type="submit">Odebrat</button>
                    </form>
                </li>
            <?php endforeach; ?>
            <?php if (count($active) === 0): ?>
                <li>Nemáte žádné aktivní pojištění.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

</body>
</html>
