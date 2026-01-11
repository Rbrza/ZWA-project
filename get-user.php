<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/insurance-catalog.php';

/**
 * Turns "nemovitost,zivotni" into "Nemovitostní, Životní"
 * If an item is not found in catalog, it keeps the original token.
 */
$id = isset($_GET['id']) ? $_GET['id'] : null;
if ($id === null || $id === '') {
    http_response_code(400);
    echo json_encode(array("error" => "Missing user ID"), JSON_UNESCAPED_UNICODE);
    exit;
}

$file = __DIR__ . '/Database.csv';
if (!file_exists($file)) {
    http_response_code(500);
    echo json_encode(array("error" => "Database file not found"), JSON_UNESCAPED_UNICODE);
    exit;
}

$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if (!$lines || count($lines) < 2) {
    http_response_code(500);
    echo json_encode(array("error" => "Database file empty"), JSON_UNESCAPED_UNICODE);
    exit;
}

$headers = str_getcsv(array_shift($lines), ';');

foreach ($lines as $line) {
    $values = str_getcsv($line, ';');
    if (count($values) !== count($headers)) continue;

    $row = array_combine($headers, $values);

    if ((string)(isset($row["id"]) ? $row["id"] : "") === (string)$id) {

        // IMPORTANT: this is the missing piece
        // Make sure insurance-catalog.php defines $INSURANCE_CATALOG
        $row['active_insurances_display'] = renderInsuranceNames(
            isset($row['active_insurances']) ? $row['active_insurances'] : '',
            isset($INSURANCE_CATALOG) ? $INSURANCE_CATALOG : array()
        );

        echo json_encode($row, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

http_response_code(404);
echo json_encode(array("error" => "User not found"), JSON_UNESCAPED_UNICODE);
