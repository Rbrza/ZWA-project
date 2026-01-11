<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/insurance-catalog.php';
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
        window.USER_ID = <?php echo json_encode($id); ?>;
    </script>
    <script src="person-details.js" defer></script>
</head>
<body>
<?php
require_once __DIR__ . '/header.php';
?>
<main>
    <div class="main-table-container">
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
        <div class="person-details-buttons">
            <a class="button-20" href="person-edit.php?id=<?php echo urlencode($id) ?>">Upravit profil</a>
            <?php if ((string)$id === (string)$_SESSION['user_id']): ?>
                <form class="log-out-form" method="POST" action="log-out.php" style="display:inline;">
                    <button class="button-20" type="submit">Odhlásit se</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
<footer>

</footer>
</body>
</html>