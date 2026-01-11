<?php
require_once __DIR__ . '/auth.php';

function fail($msg, $code = 400) {
    http_response_code($code);
    exit($msg);
}

$products = array(
    array("code" => "nemovitost", "label" => "Nemovitostní",    "price" => 250),
    array("code" => "zivotni",    "label" => "Životní",          "price" => 199),
    array("code" => "zdravotni",  "label" => "Zdravotní",        "price" => 149),
    array("code" => "povinne",    "label" => "Povinné ručení",   "price" => 320),
    array("code" => "zvirata",    "label" => "Pojištění zvířat", "price" => 180),
);

// lookup
$priceByCode = array();
foreach ($products as $p) $priceByCode[$p['code']] = (int)$p['price'];

$action = isset($_POST['action']) ? $_POST['action'] : '';
$code   = isset($_POST['code']) ? trim($_POST['code']) : '';

if ($code === '' || ($action !== 'add' && $action !== 'remove')) fail("Bad request");
if (!isset($priceByCode[$code])) fail("Unknown insurance");

$userId = (string)$_SESSION['user_id'];
$csvPath = __DIR__ . '/Database.csv';

$fh = fopen($csvPath, 'c+');
if (!$fh) fail("Cannot open database", 500);
if (!flock($fh, LOCK_EX)) { fclose($fh); fail("Database busy", 503); }

rewind($fh);
$rows = array();
while (($r = fgetcsv($fh, 0, ';')) !== false) $rows[] = $r;

if (count($rows) < 2) { flock($fh, LOCK_UN); fclose($fh); fail("Database empty", 500); }

$headers = $rows[0];
$col = array_flip($headers);

if (!isset($col['id']) || !isset($col['active_insurances']) || !isset($col['MT'])) {
    flock($fh, LOCK_UN); fclose($fh); fail("CSV missing required columns (id, active_insurances, MT)", 500);
}

$found = false;

for ($i = 1; $i < count($rows); $i++) {
    $rowId = isset($rows[$i][$col['id']]) ? (string)$rows[$i][$col['id']] : '';
    if ($rowId !== $userId) continue;

    $found = true;

    // parse current active_insurances codes
    $raw = isset($rows[$i][$col['active_insurances']]) ? $rows[$i][$col['active_insurances']] : '';
    $set = array();

    if (trim($raw) !== '') {
        foreach (explode(',', $raw) as $c) {
            $c = trim($c);
            if ($c !== '' && isset($priceByCode[$c])) $set[$c] = true; // ignore unknown codes
        }
    }

    // update set
    if ($action === 'add') $set[$code] = true;
    if ($action === 'remove' && isset($set[$code])) unset($set[$code]);

    // rebuild active_insurances
    $newCodes = array_keys($set);
    $rows[$i][$col['active_insurances']] = implode(',', $newCodes);

    // recalc MT
    $total = 0;
    foreach ($newCodes as $c) $total += $priceByCode[$c];
    $rows[$i][$col['MT']] = (string)$total;

    break;
}

if (!$found) { flock($fh, LOCK_UN); fclose($fh); fail("User not found", 404); }

// rewrite CSV
ftruncate($fh, 0);
rewind($fh);
foreach ($rows as $r) fputcsv($fh, $r, ';');
fflush($fh);

flock($fh, LOCK_UN);
fclose($fh);

header("Location: insurance-list.php");
exit;
