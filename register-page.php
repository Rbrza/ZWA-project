<?php
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
    <div class="main-registration-container">
        <h1>Registrační formulář</h1>
        <div class="registration-form-wrapper">
            <form id="form" method="POST" action="register-handler.php">
                <div class="registration-form">
                    <div>
                        <label class="inputWrap register-label">
                            Jméno:
                            <input id="register-name" type="text" name="name" placeholder="Karel" required>
                            <small class="field-error" id="err-register-name"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Příjmení:
                            <input id="register-surname" type="text" name="surname" placeholder="Nový" required>
                            <small class="field-error" id="err-register-surname"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Email:
                            <input id="register-email" type="email" name="email" placeholder="example@email.com" required>
                            <small class="field-error" id="err-register-email"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Telefon:
                            <input id="register-phone" type="tel" name="phone" placeholder="+420777888999" required>
                            <small class="field-error" id="err-register-phone"></small>
                        </label>

                        <label class="inputWrap register-label">
                            Datum narození:
                            <input id="register-DOB" type="date" name="DOB" placeholder="Datum narození" min="1900-01-01" max="<?php echo (new DateTime('today'))->modify('-18 years')->format('Y-m-d'); ?>" required>
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