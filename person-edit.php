<?php
/**
 * @file person-edit.php
 * @brief Editace profilu pojištěnce.
 *
 * Tato stránka zobrazuje formulář pro úpravu jednoho uživatele
 * (pojištěnce) identifikovaného parametrem `id` v URL.
 *
 * Data uživatele nejsou vkládána přímo do HTML,
 * ale jsou načítána JavaScriptem `person-edit.js`
 * přes AJAX z endpointu `get-user.php`.
 *
 * Po odeslání formuláře jsou změny zpracovány skriptem `update-user.php`,
 * který aktualizuje odpovídající řádek v `Database.csv`
 * a případně uloží nahranou profilovou fotografii.
 *
 * ---
 * ## Řízení přístupu
 *
 * - Administrátor (`ACType = "admin"`) může editovat libovolného uživatele.
 * - Běžný uživatel může editovat pouze svůj vlastní účet.
 * - Pokud se běžný uživatel pokusí editovat cizí ID,
 *   je automaticky přesměrován na svůj vlastní detail profilu.
 *
 * ---
 * ## Povinné parametry
 *
 * @param string|int $_GET['id'] ID uživatele uložené v `Database.csv`
 *
 * ---
 * ## Bezpečnost
 *
 * - Vyžaduje autentizaci (`auth.php`).
 * - Provádí autorizaci (admin vs. vlastní účet).
 * - ID uživatele a datumové omezení jsou do JavaScriptu vkládány pomocí
 *   `json_encode`, aby se zabránilo XSS.
 * - Nahrávání fotografií je validováno v `update-user.php`
 *   (MIME typ, velikost, přepis souboru).
 *
 * @see auth.php
 * @see get-user.php
 * @see update-user.php
 * @see person-edit.js
 */

require_once __DIR__ . '/auth.php';
$id = $_GET['id'];

if ($id === null) {
    http_response_code(400);
    exit("Missing user ID");
}


$isAdmin = (isset($_SESSION['ACType']) && $_SESSION['ACType'] === "admin");
$myId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// If not admin and trying to access someone else → redirect to own page
if (!$isAdmin && (string)$id !== (string)$myId) {
    header("Location: person-details.php?id=" . urlencode($myId));
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detaily člověka</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script>
        /**
         * @var string|number USER_ID
         * @brief ID uživatele, který se právě edituje.
         *
         * Používá ho `person-edit.js` k načtení dat uživatele
         * z `get-user.php?id=...`.
         */
        window.USER_ID = <?php echo json_encode($id); ?>;

        /**
         * @var string MAX_DOB
         * @brief Maximální povolené datum narození (18+).
         *
         * Hodnota ve formátu YYYY-MM-DD.
         * Používá se na klientovi jako `max` pro `<input type="date">`,
         * aby uživatel nemohl vybrat mladší než 18 let.
         *
         * Serverová kontrola je ale vždy provedena znovu v `update-user.php`.
         */
        window.MAX_DOB = "<?php echo (new DateTime('today'))->modify('-18 years')->format('Y-m-d'); ?>";
    </script>
    <script src="person-edit.js" defer></script>
</head>
<body>
<?php
require_once __DIR__ . '/header.php';
?>
<main>
    <form method="POST" action="update-user.php" enctype="multipart/form-data">

        <div class="main-table-container">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
            <label class="inputWrap register-label">
                Profilová fotka:
                <input type="file" name="photo" accept="image/*">
            </label>
            <table>
                <caption>Detail osoby</caption>
                <tbody id="main-table-tbody-person-details">

                <!--
                <tfoot>
                <tr>
                    <th scope="row" colspan="3">Total</th>
                    <td>4200</td>
                </tr>
                </tfoot>
                -->
            </table>
            <button class="button-20" type="submit">Uložit změny</button>
        </div>
    </form>
</main>
<footer>

</footer>
</body>
</html>