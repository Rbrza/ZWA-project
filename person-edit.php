<?php
/**
 * Person edit page (GET).
 *
 * Displays an edit form for a single user, prefilled by person-edit.js via get-user.php.
 * Submits changes to update-user.php (POST).
 *
 * Access control:
 * - Admins may edit any user.
 * - Non-admin users may only edit their own user row.
 *   If a non-admin tries to edit another user's id, they are redirected to their own details.
 *
 * Required query parameters:
 * - id (string|int): user id from Database.csv.
 *
 * Security:
 * - Requires authentication (auth.php).
 * - Authorization is enforced here.
 * - id is injected into JS using json_encode to prevent XSS.
 * - Photo uploads are handled by update-user.php.
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
         * USER_ID and MAX_DOB are consumed by person-edit.js.
         * - USER_ID: selects which user record to load.
         * - MAX_DOB: HTML date input max value to enforce 18+ (client-side only).
         */
        window.USER_ID = <?php echo json_encode($id); ?>;
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