<?php
/**
 * Registration page (GET).
 *
 * Displays the registration form and renders server-side validation feedback
 * using flash values stored in session by register-handler.php.
 *
 * Flash session variables (consumed on page load):
 * - $_SESSION['reg_old']         : array of previous user-entered values (excluding password)
 * - $_SESSION['reg_error']       : error message to display
 * - $_SESSION['reg_error_field'] : key of the invalid field to highlight
 *
 * Security:
 * - All flash output is escaped via htmlspecialchars to prevent XSS.
 * - Password inputs are never prefilled (requirement).
 */
if (!isset($_SESSION)) session_start();

$old = isset($_SESSION['reg_old']) ? $_SESSION['reg_old'] : array();
$err = isset($_SESSION['reg_error']) ? $_SESSION['reg_error'] : '';
$errField = isset($_SESSION['reg_error_field']) ? $_SESSION['reg_error_field'] : '';

// clear flash after reading
unset($_SESSION['reg_old'], $_SESSION['reg_error'], $_SESSION['reg_error_field']);
/**
 * Returns an escaped "old value" for a given field key.
 *
 * @param string $key Field name.
 * @param array  $old Old values array from session.
 * @return string Escaped HTML value.
 */
function oldv($key, $old) {
  return isset($old[$key]) ? htmlspecialchars($old[$key], ENT_QUOTES, 'UTF-8') : '';
}
/**
 * Returns a CSS class to mark an invalid field.
 *
 * @param string $key Field key for the current input.
 * @param string $errField Field key that was invalid.
 * @return string CSS class name (or empty string).
 */
function isBad($key, $errField) {
  return ($key === $errField) ? ' error' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Správa faktur</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <script src="register-page.js" defer></script>

</head>
<body>
<?php
require_once __DIR__ . '/header.php';
?>
<main>
    <?php if ($err !== ''): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($err, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <div class="main-registration-container">
        <h1>Registrační formulář</h1>
        <div class="registration-form-wrapper">
            <form id="form" method="POST" action="register-handler.php">
                <div class="registration-form">
                    <div>
                        <label class="inputWrap register-label">
                            Jméno:
                            <input id="register-name" type="text" name="name" placeholder="Karel"
                                   value="<?php echo oldv('name', $old); ?>"
                                   class="<?php echo isBad('name', $errField); ?>"
                                   required>
                            <small class="field-error" id="err-register-name"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Příjmení:
                            <input id="register-surname" type="text" name="surname" placeholder="Nový"
                                   value="<?php echo oldv('surname', $old); ?>"
                                   class="<?php echo isBad('surname', $errField); ?>"
                                   required>
                            <small class="field-error" id="err-register-surname"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Email:
                            <input id="register-email" type="email" name="email" placeholder="example@email.com"
                                   value="<?php echo oldv('email', $old); ?>"
                                   class="<?php echo isBad('email', $errField); ?>"
                                   required>
                            <small class="field-error" id="err-register-email"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Telefon:
                            <input id="register-phone" type="tel" name="phone" placeholder="+420777888999"
                                   value="<?php echo oldv('phone', $old); ?>"
                                   class="<?php echo isBad('phone', $errField); ?>"
                                   required>
                            <small class="field-error" id="err-register-phone"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Datum narození:
                            <input id="register-DOB" type="date" name="DOB" placeholder="Datum narození"
                                   min="1900-01-01"
                                   max="<?php echo (new DateTime('today'))->modify('-18 years')->format('Y-m-d'); ?>"
                                   value="<?php echo oldv('DOB', $old); ?>"
                                   class="<?php echo isBad('DOB', $errField); ?>"
                                   required>
                            <small class="field-error" id="err-register-DOB"></small>
                        </label>
                    </div>

                    <div class="registration-form-password-wrapper">

                        <label class="inputWrap register-label">
                            Heslo:
                            <input id="password" type="password" name="password" placeholder="Heslo" required>
                            <small class="field-error" id="err-password"></small>
                        </label>

                        <label id="password2-wrap" class="inputWrap register-label">
                            Potrvďte heslo:
                            <input id="password2" type="password" name="password2" placeholder="Heslo" required>
                            <small class="field-error" id="err-password2"></small>
                        </label>

                    </div>

                </div>

                <button type="submit" class="button-20 create-account-button">Create account</button>

            </form>
        </div>
        <div class="login-button-wrapper">
            <a class="button-20" href="login-page.php">Přihlásit se</a>
        </div>
    </div>
</main>
<footer>

</footer>
</body>
</html>