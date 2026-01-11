<?php
/**
 * Login page (GET).
 *
 * Displays the login form and any flash error message produced by login-handler.php.
 *
 * Flash session variables (consumed on page load):
 * - $_SESSION['login_error']     : string error message to show above the form
 * - $_SESSION['login_old_email'] : string email value used to prefill the email field
 *
 * Security:
 * - Error text and old email are escaped with htmlspecialchars() to prevent XSS.
 */
if (!isset($_SESSION)) session_start();

$err = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
$oldEmail = isset($_SESSION['login_old_email']) ? $_SESSION['login_old_email'] : '';

unset($_SESSION['login_error'], $_SESSION['login_old_email']);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Správa faktur</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="index.js" defer></script>

</head>
<body>
<?php
require_once __DIR__ . '/header.php';
?>
<main>
    <div class="main-registration-container">
        <h1>Přihlašovací formulář</h1>
        <?php if ($err !== ''): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <div class="registration-form-wrapper">
            <form id="form" method="POST" action="login-handler.php">
                <div class="login-form">
                    <div>
                        <label class="inputWrap">
                            Email:
                            <input type="email" name="email" placeholder="example@email.com"
                                   value="<?php echo htmlspecialchars($oldEmail, ENT_QUOTES, 'UTF-8'); ?>"
                                   required>
                        </label>

                        <label class="inputWrap">
                            Heslo:
                            <input id="password" type="password" name="password" placeholder="Heslo" required>
                        </label>
                    </div>

                    <button type="submit" class="button-20 create-account-button">Přihlásit se</button>

                </div>
            </form>
        </div>
        <div class="register-button-wrapper">
            <a class="button-20" href="register-page.php">Vytvořit nový účet</a>
        </div>
    </div>
</main>
<footer>

</footer>
</body>
</html>