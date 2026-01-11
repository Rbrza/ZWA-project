<?php
require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(array("error" => $msg));
    exit;
}

// permissions
$isAdmin = (isset($_SESSION['ACType']) && $_SESSION['ACType'] === 'admin');

if (!$isAdmin) {
    fail("Forbidden", 403);
}

$id = isset($_POST['id']) ? $_POST['id'] : null;
if ($id === null || $id === '') fail("Missing id");

$csv = __DIR__ . "/Database.csv";
$fh = fopen($csv, "c+");
if (!$fh) fail("Cannot open database", 500);

if (!flock($fh, LOCK_EX)) {
    fclose($fh);
    fail("Database busy", 503);
}

rewind($fh);
$rows = array();
while (($r = fgetcsv($fh, 0, ";")) !== false) {
    $rows[] = $r;
}

if (count($rows) < 2) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("Database empty", 500);
}

$headers = $rows[0];
$col = array_flip($headers);
if (!isset($col['id'])) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("CSV missing id column", 500);
}

$newRows = array();
$deleted = false;

foreach ($rows as $i => $row) {
    if ($i === 0) { // header
        $newRows[] = $row;
        continue;
    }

    $rowId = isset($row[$col['id']]) ? (string)$row[$col['id']] : '';
    if ($rowId === (string)$id) {
        $deleted = true;
        continue; // skip this row
    }

    $newRows[] = $row;
}

if (!$deleted) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("User not found", 404);
}

// rewrite CSV
ftruncate($fh, 0);
rewind($fh);
foreach ($newRows as $r) {
    fputcsv($fh, $r, ";");
}
fflush($fh);

flock($fh, LOCK_UN);
fclose($fh);

echo json_encode(array("ok" => true));
