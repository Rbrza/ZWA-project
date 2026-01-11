<?php
/**
 * @file update-user.php
 * @brief Aktualizace uživatelského profilu (POST).
 *
 * Tento handler aktualizuje editovatelné údaje uživatele uložené v CSV databázi (`Database.csv`)
 * a volitelně zpracuje nahrání profilové fotografie.
 *
 * Upravované položky (pokud existují v CSV hlavičce):
 * - `name`, `surname`, `DOB`, `email`, `phone`, `ICO`
 * - `photo` (jen pokud sloupec existuje a byl nahrán soubor)
 *
 * ---
 * ## Vstup (POST / FILES)
 * - `POST id` (string|int) – ID upravovaného uživatele
 * - `POST name` (string) – jméno
 * - `POST surname` (string) – příjmení
 * - `POST DOB` (string) – datum narození ve formátu YYYY-MM-DD
 * - `POST email` (string) – email
 * - `POST phone` (string) – telefon (E.164-like)
 * - `POST ICO` (string) – volitelné
 * - `FILES photo` (optional) – profilová fotka
 *
 * ---
 * ## Autorizace
 * - Admin (`ACType = "admin"`) může upravit libovolného uživatele.
 * - Běžný uživatel může upravit pouze sám sebe (`$_SESSION['user_id']`).
 *
 * ---
 * ## Uložení dat
 * - `Database.csv` je přepsána metodou read-modify-write.
 * - Zápis probíhá pod exkluzivním zámkem `flock(LOCK_EX)`.
 * - Fotka je ukládána do `/uploads` jako stabilní jméno `profile_{id}.ext`
 *   (předchozí fotka se přepíše).
 * - Do CSV se ukládá relativní cesta např. `uploads/profile_12.jpg`.
 *
 * ---
 * ## Validace
 * - Server-side kontrola: délka jména/příjmení, email, telefon, datum narození (18+)
 * - Ochrana proti CSV injection u textových polí (`clean()`).
 *
 * ---
 * ## Výstup (PRG)
 * - Po úspěchu provede redirect na `person-details.php?id=...`
 *   (POST-Redirect-GET).
 *
 * ---
 * ## Bezpečnost
 * - Soubor vyžaduje přihlášení (`auth.php`).
 * - Upload obrázku je ověřen podle obsahu (`getimagesize()`), typu a velikosti.
 * - Neprovádí se změna `passwordHash` (pokud se výslovně neimplementuje zvlášť).
 *
 * @see auth.php
 * @see person-edit.php
 * @see person-details.php
 */
require_once __DIR__ . '/auth.php';

/**
 * Ukončí request s chybovou zprávou a HTTP kódem.
 *
 * @param string $msg  Text chyby.
 * @param int    $code HTTP status kód (výchozí 400).
 * @return void
 */
function fail($msg, $code = 400)
{
    http_response_code($code);
    echo $msg;
    exit;
}
/**
 * Uloží nahranou profilovou fotku a vrátí cestu pro uložení do CSV.
 *
 * Chování:
 * - pokud nebyl nahrán soubor → vrací `null`
 * - ověří upload, maximální velikost a typ souboru podle obsahu (getimagesize)
 * - podporuje JPEG/PNG/WEBP
 * - ukládá do `/uploads` a přepisuje předchozí fotku daného uživatele
 *
 * @param string|int $userId ID uživatele (použije se pro jméno souboru).
 * @return string|null Relativní cesta pro web (např. `uploads/profile_12.jpg`) nebo null.
 */
function saveUploadedPhoto($userId) {
    if (!isset($_FILES['photo']) || !is_array($_FILES['photo'])) return null;
    if (!isset($_FILES['photo']['error']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) return null;

    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        fail("Chyba při nahrávání fotky.");
    }

    // Validate size (e.g. 2MB)
    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        fail("Fotka je příliš velká (max 2MB).");
    }

    // Validate image type by content
    $tmp = $_FILES['photo']['tmp_name'];
    $info = @getimagesize($tmp);
    if ($info === false) {
        fail("Soubor není obrázek.");
    }

    $mime = $info['mime'];
    $ext = null;
    if ($mime === 'image/jpeg') $ext = 'jpg';
    else if ($mime === 'image/png') $ext = 'png';
    else if ($mime === 'image/webp') $ext = 'webp';
    else {
        fail("Nepodporovaný formát (použij JPG/PNG/WEBP).");
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            fail("Nelze vytvořit složku uploads.", 500);
        }
    }

    // Always overwrite previous photo for that user
    $filename = 'profile_' . preg_replace('/\D+/', '', (string)$userId) . '.' . $ext;
    $destFs = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($tmp, $destFs)) {
        fail("Nelze uložit fotku.", 500);
    }

    // Path to store in CSV (relative for web)
    return 'uploads/' . $filename;
}

/**
 * Vyčistí textový vstup pro uložení do CSV a zabrání CSV injection.
 *
 * CSV injection hrozí při otevření CSV v Excelu (hodnoty začínající znaky `= + - @`),
 * proto se taková hodnota prefixuje apostrofem.
 *
 * @param mixed $v Vstupní hodnota.
 * @return string Očištěný řetězec připravený pro uložení.
 */
function clean($v)
{
    $v = trim((string)$v);
    if ($v !== '' && in_array($v[0], array('=', '+', '-', '@'), true)) {
        $v = "'" . $v;
    }
    return $v;
}

$id = isset($_POST['id']) ? clean($_POST['id']) : null;
$photoPath = saveUploadedPhoto($id);
$name = isset($_POST['name']) ? clean($_POST['name']) : '';
$surname = isset($_POST['surname']) ? clean($_POST['surname']) : '';
$dobStr = isset($_POST['DOB']) ? clean($_POST['DOB']) : '';
$email = isset($_POST['email']) ? trim((string)$_POST['email']) : '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail("Neplatný email.");
$email = clean($email);

$phoneRaw = isset($_POST['phone']) ? $_POST['phone'] : '';
$phone = trim((string)$phoneRaw);
$phone = preg_replace('/\s+/', '', $phone);
$phone = str_replace(array('-', '(', ')'), '', $phone);

$ico = isset($_POST['ICO']) ? clean($_POST['ICO']) : '';

// length rules
if (mb_strlen($name, 'UTF-8') < 2) {
    fail("Jméno musí mít alespoň 2 znaky.");
}
if (mb_strlen($surname, 'UTF-8') < 2) {
    fail("Příjmení musí mít alespoň 2 znaky.");
}

if (strlen($name) > 50) fail("Jméno je příliš dlouhé.");
if (strlen($surname) > 50) fail("Příjmení je příliš dlouhé.");



if ($id === null || $id === '') fail("Missing user ID");
if ($name === '' || $surname === '' || $dobStr === '' || $email === '' || $phone === '') {
    fail("Vyplňte všechna povinná pole.");
}

/* ---- permission ---- */
$isAdmin = (isset($_SESSION['ACType']) && $_SESSION['ACType'] === 'admin');
$myId = isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : '';

if (!$isAdmin && (string)$id !== $myId) {
    fail("Forbidden", 403);
}

/* ---- validations ---- */
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail("Neplatný email.");

if (!preg_match('/^\+?[1-9]\d{7,14}$/', $phone)) {
    fail("Neplatné telefonní číslo. Použijte formát např. +420777888999.");
}

$dob = DateTime::createFromFormat('Y-m-d', $dobStr);
$errors = DateTime::getLastErrors();
$warningCount = 0;
$errorCount = 0;
if (is_array($errors)) {
    if (isset($errors['warning_count'])) $warningCount = $errors['warning_count'];
    if (isset($errors['error_count'])) $errorCount = $errors['error_count'];
}
if (!$dob || $warningCount > 0 || $errorCount > 0) fail("Neplatné datum narození.");

$dob->setTime(0, 0, 0);
$cutoff = new DateTime('today');
$cutoff->modify('-18 years');
if ($dob > $cutoff) fail("Uživatel musí mít alespoň 18 let.");

/* ---- CSV update ---- */
$csvPath = __DIR__ . '/Database.csv';
$fh = fopen($csvPath, 'c+');
if (!$fh) fail("Nelze otevřít databázi.", 500);

if (!flock($fh, LOCK_EX)) {
    fclose($fh);
    fail("Databáze je zaneprázdněná.", 503);
}

rewind($fh);
$rows = array();
while (($r = fgetcsv($fh, 0, ';')) !== false) {
    $rows[] = $r;
}

if (count($rows) < 2) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("Database file empty.", 500);
}

$headers = $rows[0];
$col = array_flip($headers);

if (!isset($col['id'])) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("CSV missing id column.", 500);
}

/* find row by id + check email uniqueness (if changed) */
$targetIndex = -1;

for ($i = 1; $i < count($rows); $i++) {
    $rowId = isset($rows[$i][$col['id']]) ? (string)$rows[$i][$col['id']] : '';
    if ($rowId === (string)$id) {
        $targetIndex = $i;
    }
}

if ($targetIndex === -1) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("User not found.", 404);
}

// Email uniqueness: allow same email for this user, but not for others
if (isset($col['email'])) {
    for ($i = 1; $i < count($rows); $i++) {
        if ($i === $targetIndex) continue;
        $existingEmail = isset($rows[$i][$col['email']]) ? $rows[$i][$col['email']] : '';
        if (strcasecmp((string)$existingEmail, (string)$email) === 0) {
            flock($fh, LOCK_UN);
            fclose($fh);
            fail("Email už existuje.");
        }
    }
}

/* update only editable fields */
if (isset($col['photo']) && $photoPath !== null) {
    $rows[$targetIndex][$col['photo']] = $photoPath;
}
if (isset($col['name'])) $rows[$targetIndex][$col['name']] = $name;
if (isset($col['surname'])) $rows[$targetIndex][$col['surname']] = $surname;
if (isset($col['DOB'])) $rows[$targetIndex][$col['DOB']] = $dobStr;
if (isset($col['email'])) $rows[$targetIndex][$col['email']] = $email;
if (isset($col['phone'])) $rows[$targetIndex][$col['phone']] = $phone;
if (isset($col['ICO'])) $rows[$targetIndex][$col['ICO']] = $ico;

// IMPORTANT: do NOT overwrite passwordHash unless user is changing password!
// (so we leave passwordHash as-is)

/* rewrite file */
ftruncate($fh, 0);
rewind($fh);
for ($i = 0; $i < count($rows); $i++) {
    fputcsv($fh, $rows[$i], ';');
}
fflush($fh);

flock($fh, LOCK_UN);
fclose($fh);

/* optional: if user changed their own email, update session */
if ((string)$id === $myId) {
    $_SESSION['email'] = $email;
}

header("Location: person-details.php?id=" . urlencode($id));
exit;
