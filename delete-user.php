<?php
/**
 * @file delete-user.php
 * @brief API endpoint pro smazání uživatele z databáze.
 *
 * Tento skript přijímá HTTP POST požadavek obsahující ID uživatele a
 * odstraní odpovídající řádek ze souboru `Database.csv`.
 *
 * Výstupem je JSON odpověď:
 * - `{ "ok": true }` při úspěchu
 * - `{ "error": "..." }` při chybě
 *
 * Endpoint je určen pro volání z administrátorského rozhraní
 * (např. tlačítko **Smazat** v `index.php` volané přes `fetch()`).
 *
 * ### Bezpečnost
 * - Vyžaduje přihlášení (`auth.php`).
 * - Vyžaduje administrátorská oprávnění (`$_SESSION['ACType'] === 'admin'`).
 * - Používá `flock()` pro ochranu CSV před souběžným zápisem.
 *
 * ### Vstup
 * - `POST id` – identifikátor uživatele (sloupec `id` v `Database.csv`).
 *
 * ### Výstup
 * - JSON objekt s klíčem `ok` nebo `error`.
 *
 * @see auth.php
 * @see index.php
 */

require_once __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

/**
 * Odešle JSON chybovou odpověď a ukončí skript.
 *
 * @param string $msg  Text chyby pro uživatele.
 * @param int    $code HTTP status kód (výchozí 400).
 * @return void
 */
function fail($msg, $code = 400) {
    http_response_code($code);
    echo json_encode(array("error" => $msg));
    exit;
}

/* --- Ověření oprávnění --- */
$isAdmin = (isset($_SESSION['ACType']) && $_SESSION['ACType'] === 'admin');

if (!$isAdmin) {
    fail("Forbidden", 403);
}

/* --- Načtení vstupu --- */
$id = isset($_POST['id']) ? $_POST['id'] : null;
if ($id === null || $id === '') {
    fail("Missing id");
}

/* --- Otevření CSV --- */
$csv = __DIR__ . "/Database.csv";
$fh = fopen($csv, "c+");
if (!$fh) {
    fail("Cannot open database", 500);
}

if (!flock($fh, LOCK_EX)) {
    fclose($fh);
    fail("Database busy", 503);
}

/* --- Načtení všech řádků --- */
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

/* --- Filtrování (odstranění uživatele) --- */
$newRows = array();
$deleted = false;

foreach ($rows as $i => $row) {
    if ($i === 0) { // hlavička
        $newRows[] = $row;
        continue;
    }

    $rowId = isset($row[$col['id']]) ? (string)$row[$col['id']] : '';
    if ($rowId === (string)$id) {
        $deleted = true;
        continue; // přeskočíme tento řádek = smazání
    }

    $newRows[] = $row;
}

if (!$deleted) {
    flock($fh, LOCK_UN);
    fclose($fh);
    fail("User not found", 404);
}

/* --- Přepis CSV --- */
ftruncate($fh, 0);
rewind($fh);
foreach ($newRows as $r) {
    fputcsv($fh, $r, ";");
}
fflush($fh);

flock($fh, LOCK_UN);
fclose($fh);

/* --- Výstup --- */
echo json_encode(array("ok" => true));
