<?php
/**
 * @file insurance-list.php
 * @brief Stránka správy pojištění pro přihlášeného uživatele.
 *
 * Tato stránka umožňuje přihlášenému uživateli přidávat a odebírat pojištění.
 * Aktivní pojištění se ukládají v CSV databázi (`Database.csv`) do sloupce:
 * - `active_insurances` – čárkou oddělený seznam kódů pojištění (např. `nemovitost,zivotni`)
 *
 * Celková měsíční částka se ukládá do sloupce:
 * - `MT` – součet měsíčních cen všech aktivních pojištění (CZK)
 *
 * ### Algoritmus
 * - Načte ID uživatele ze session (`$_SESSION['user_id']`).
 * - Najde řádek uživatele v `Database.csv`.
 * - Rozparsuje `active_insurances` na množinu kódů.
 * - Rozdělí katalog pojištění na dvě části:
 *   - dostupná pojištění (uživatel je ještě nemá)
 *   - aktivní pojištění (uživatel je má)
 * - Vypočte `totalMonthly` jako součet cen aktivních pojištění (zobrazení v UI).
 *
 * ### Aktualizace dat (POST-Redirect-GET)
 * Tlačítka Přidat/Odebrat odesílají formulář (POST) na `update-insurance.php`,
 * který provede změnu a následně přesměruje zpět na tuto stránku (PRG),
 * čímž se zabrání opětovnému odeslání dat při refreshi.
 *
 * ### Bezpečnost
 * - Vyžaduje přihlášení (`auth.php`).
 * - Veškerý výstup do HTML je escapovaný přes `htmlspecialchars()` (ochrana proti XSS).
 *
 * @see auth.php
 * @see header.php
 * @see insurance-catalog.php
 * @see update-insurance.php
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
 * Escapuje text pro bezpečný výpis do HTML.
 *
 * Používá se pro všechny hodnoty, které se vypisují do HTML, aby se zabránilo XSS.
 *
 * @param mixed $s Vstupní hodnota (bude převedena na string).
 * @return string Escapovaný UTF-8 text (ENT_QUOTES).
 */
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

/**
 * Načte z CSV databáze konkrétního uživatele podle ID.
 *
 * CSV je odděleno středníkem (`;`). První řádek je hlavička.
 * Funkce postupně čte řádky a porovnává hodnotu sloupce `id`.
 *
 * @param string $path Absolutní cesta k `Database.csv`.
 * @param string $id   ID uživatele, které chceme načíst.
 *
 * @return array|null Asociativní pole (hlavička => hodnota), nebo `null` pokud uživatel neexistuje.
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
                    <form method="POST" action="update-insurance.php" class="form-pagination">
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
        <div class="mt-tax-div">
            Celkem měsíčně (MT): <b><?php echo h($totalMonthly); ?> Kč</b>
        </div>

        <ul>
            <?php foreach ($active as $p): ?>
                <li>
                    <?php echo h($p['label']); ?> (<?php echo h($p['price']); ?> Kč / měs.)
                    <form method="POST" action="update-insurance.php" class="form-pagination">
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
