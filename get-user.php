<?php
/**
 * @file get-user.php
 * @brief Read-only API endpoint pro načtení jednoho uživatele z CSV databáze.
 *
 * Endpoint vrací záznam uživatele z `Database.csv` podle query parametru `?id=...`.
 * Používá se zejména v `person-details.js` a `person-edit.js` k naplnění UI bez vkládání
 * dat přímo do HTML.
 *
 * ### HTTP odpovědi
 * - **200 OK**: JSON objekt nalezeného uživatele (včetně dopočtených polí jako `active_insurances_display`)
 * - **400 Bad Request**: `{ "error": "Missing user ID" }`
 * - **404 Not Found**: `{ "error": "User not found" }`
 * - **500 Internal Server Error**:
 *   - `{ "error": "Database file not found" }`
 *   - `{ "error": "Database file empty" }`
 *
 * ### Bezpečnostní poznámky
 * - Tento endpoint sám o sobě nevynucuje autentizaci. Přístup se kontroluje na úrovni stránek
 *   (např. `person-details.php`). Pokud bys endpoint vystavil veřejně, přidej sem kontrolu `auth.php`.
 * - Na frontendu zobrazuj hodnoty přes `textContent` a DOM API (ne přes `innerHTML`) kvůli XSS.
 *
 * @see person-details.php
 * @see person-edit.php
 * @see person-details.js
 * @see person-edit.js
 * @see insurance-catalog.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/insurance-catalog.php';

/**
 * Vytvoří uživatelsky čitelný seznam názvů pojištění z kódů uložených v CSV.
 *
 * Např. vstup `"nemovitost,zivotni"` převede na `"Nemovitostní, Životní"`.
 * Pokud kód není v katalogu, ignoruje se (nebo lze ponechat token – dle implementace katalogu).
 *
 * @param string $activeCodes Seznam kódů pojištění oddělený čárkou.
 * @return string Seznam názvů pojištění oddělený čárkou.
 *
 * @note Funkce `renderInsuranceNames()` typicky pochází z `insurance-catalog.php`.
 *       Pokud ji tam máš, tuto lokální funkci můžeš odstranit a volat přímo tu z katalogu.
 */
// (Pokud už renderInsuranceNames existuje v insurance-catalog.php, tak tuhle funkci smaž!)
// function renderInsuranceNames($activeCodes, $catalog) { ... }

/* --- Vstup --- */
$id = isset($_GET['id']) ? $_GET['id'] : null;
if ($id === null || $id === '') {
    http_response_code(400);
    echo json_encode(array("error" => "Missing user ID"), JSON_UNESCAPED_UNICODE);
    exit;
}

/* --- Načtení databáze --- */
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

/* --- Vyhledání uživatele --- */
foreach ($lines as $line) {
    $values = str_getcsv($line, ';');
    if (count($values) !== count($headers)) {
        continue; // přeskoč rozbité řádky
    }

    $row = array_combine($headers, $values);

    $rowId = isset($row['id']) ? (string)$row['id'] : '';
    if ($rowId === (string)$id) {

        /* --- Dopočtená pole pro UI --- */
        $row['active_insurances_display'] = renderInsuranceNames(
            isset($row['active_insurances']) ? $row['active_insurances'] : '',
            isset($INSURANCE_CATALOG) ? $INSURANCE_CATALOG : array()
        );

        echo json_encode($row, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/* --- Nenalezeno --- */
http_response_code(404);
echo json_encode(array("error" => "User not found"), JSON_UNESCAPED_UNICODE);
