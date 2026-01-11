<?php
/**
 * @file register-page.php
 * @brief Registrační formulář nového uživatele.
 *
 * Tato stránka zobrazuje HTML formulář pro vytvoření nového účtu
 * pojištěnce. Při chybě validace je uživatel přesměrován zpět
 * ze skriptu `register-handler.php` pomocí POST-Redirect-GET
 * a jsou zobrazeny chybové hlášky a původně zadané hodnoty.
 *
 * ---
 * ## Flash proměnné v session
 *
 * Tyto hodnoty jsou nastaveny v `register-handler.php`
 * a po vykreslení této stránky jsou zrušeny:
 *
 * @var array  $_SESSION['reg_old']
 *  Pole starých hodnot formuláře (bez hesel).
 *
 * @var string $_SESSION['reg_error']
 *  Text chybové zprávy k zobrazení uživateli.
 *
 * @var string $_SESSION['reg_error_field']
 *  Klíč pole formuláře, které je neplatné a má být zvýrazněno.
 *
 * ---
 * ## Bezpečnost
 *
 * - Veškerý obsah ze session je vypisován pomocí `htmlspecialchars()`
 *   aby bylo zabráněno XSS.
 * - Hesla nejsou nikdy předvyplňována.
 *
 * @see register-handler.php
 */

if (!isset($_SESSION)) session_start();

$old = isset($_SESSION['reg_old']) ? $_SESSION['reg_old'] : array();
$err = isset($_SESSION['reg_error']) ? $_SESSION['reg_error'] : '';
$errField = isset($_SESSION['reg_error_field']) ? $_SESSION['reg_error_field'] : '';

// clear flash after reading
unset($_SESSION['reg_old'], $_SESSION['reg_error'], $_SESSION['reg_error_field']);
/**
 * @brief Vrátí escaped starou hodnotu formuláře.
 *
 * Používá se pro předvyplnění formuláře po chybě validace
 * (POST-Redirect-GET).
 *
 * @param string $key Název pole (např. "email", "name").
 * @param array  $old Pole hodnot uložené v $_SESSION['reg_old'].
 * @return string Bezpečný HTML řetězec (escaped).
 */
function oldv($key, $old) {
  return isset($old[$key]) ? htmlspecialchars($old[$key], ENT_QUOTES, 'UTF-8') : '';
}
/**
 * @brief Vrátí CSS třídu pro zvýraznění chybného pole.
 *
 * Pokud klíč aktuálního pole odpovídá klíči chyby,
 * vrátí "error", jinak prázdný řetězec.
 *
 * @param string $key      Název aktuálního pole.
 * @param string $errField Klíč pole, které je neplatné.
 * @return string Název CSS třídy nebo prázdný řetězec.
 */
function isBad($key, $errField) {
  return ($key === $errField) ? ' error' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registrace osoby</title>
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