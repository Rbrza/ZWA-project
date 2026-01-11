<?php
function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(["error" => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function clean($v) {
    $v = trim((string)$v);
    // prevent CSV injection (Excel formulas)
    if ($v !== '' && in_array($v[0], ['=', '+', '-', '@'], true)) {
        $v = "'" . $v;
    }
    return $v;
}

$name    = clean($_POST['name']);
$surname = clean($_POST['surname']);
$dobStr  = clean($_POST['DOB']);
$email   = clean($_POST['email']);
$phone   = clean($_POST['phone']);
$password = password_hash(clean($_POST['password']),PASSWORD_DEFAULT);

if ($name === '' || $surname === '' || $dobStr === '' || $email === '' || $phone === '' || $password === '') {
    fail("Vyplňte všechna povinná pole.");
}

// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail("Neplatný email.");
}

// Phone validation (strict E.164-ish: +? then 8..15 digits total, no spaces)
if (!preg_match('/^\+?[1-9]\d{7,14}$/', $phone)) {
    fail("Neplatné telefonní číslo. Použijte formát např. +420777888999.");
}

$dob = DateTime::createFromFormat('Y-m-d', $dobStr);
$errors = DateTime::getLastErrors();

$warningCount = 0;
$errorCount = 0;

if (is_array($errors)) {
    if (isset($errors['warning_count'])) {
        $warningCount = $errors['warning_count'];
    }
    if (isset($errors['error_count'])) {
        $errorCount = $errors['error_count'];
    }
}

if (!$dob || $warningCount > 0 || $errorCount > 0) {
    fail("Neplatné datum narození.");
}
$dob->setTime(0, 0, 0);

$cutoff = new DateTime('today');
$cutoff->modify('-18 years');
if ($dob > $cutoff) {
    fail("Uživatel musí mít alespoň 18 let.");
}

// --- CSV setup ---
$csvPath = __DIR__ . '/Database.csv';

$headers = [
    "id","name","surname","DOB","email","phone","ICO","MT","score",
    "active_insurances","ACType","passwordHash"
];

// Default values for fields that aren't collected in the form yet:
$ICO = "";
$MT = "";
$score = "";
$active_insurances = "";
$ACType = "user";

// Open file
$fh = fopen($csvPath, 'c+'); // create if not exists
if (!$fh) fail("Nelze otevřít databázi.", 500);

// Lock file
if (!flock($fh, LOCK_EX)) {
    fclose($fh);
    fail("Databáze je zaneprázdněná, zkuste to znovu.", 503);
}

// Read existing contents
rewind($fh);
$lines = [];
while (($row = fgetcsv($fh, 0, ';')) !== false) {
    $lines[] = $row;
}

// If empty, write header first
if (count($lines) === 0) {
    rewind($fh);
    fputcsv($fh, $headers, ';');
    fflush($fh);
    $lines[] = $headers;
}

$existingHeaders = $lines[0];

// Map column index
$colIndex = array_flip($existingHeaders);
if (!isset($colIndex['id'], $colIndex['email'])) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("CSV header is missing required columns (id, email).", 500);
}

// Check email uniqueness + find max id
$maxId = -1;
for ($i = 1; $i < count($lines); $i++) {
    $r = $lines[$i];
    $existingEmail = $r[$colIndex['email']];
    if (strcasecmp((string)$existingEmail, $email) === 0) {
        flock($fh, LOCK_UN);
        fclose($fh);
        fail("Email už existuje.");
    }
    $existingId = $r[$colIndex['id']];
    if (is_numeric($existingId)) $maxId = max($maxId, (int)$existingId);
}

$newId = $maxId + 1;

// Build new row aligned to header columns
$newRow = array_fill(0, count($existingHeaders), "");
$newRow[$colIndex['id']] = (string)$newId;
$newRow[$colIndex['name']] = $name;
$newRow[$colIndex['surname']] = $surname;
$newRow[$colIndex['DOB']] = $dobStr;
$newRow[$colIndex['email']] = $email;
$newRow[$colIndex['phone']] = $phone;

if (isset($colIndex['ICO'])) $newRow[$colIndex['ICO']] = $ICO;
if (isset($colIndex['MT'])) $newRow[$colIndex['MT']] = $MT;
if (isset($colIndex['score'])) $newRow[$colIndex['score']] = $score;
if (isset($colIndex['active_insurances'])) $newRow[$colIndex['active_insurances']] = $active_insurances;
if (isset($colIndex['ACType'])) $newRow[$colIndex['ACType']] = $ACType;
if (isset($colIndex['passwordHash'])) $newRow[$colIndex['passwordHash']] = $password;

// Append at end (move pointer to end)
fseek($fh, 0, SEEK_END);
fputcsv($fh, $newRow, ';');
fflush($fh);

flock($fh, LOCK_UN);
fclose($fh);

header("Location: login-page.php");
exit;