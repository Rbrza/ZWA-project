<?php
/**
 * Login handler (POST).
 *
 * Validates email/password against Database.csv and establishes an authenticated session.
 * Uses the POST-Redirect-GET pattern:
 * - On failure: stores a flash message + old email in session and redirects back to login-page.php.
 * - On success: regenerates session id, stores login state, and redirects to person-details.php.
 *
 * Inputs:
 * - POST email    (string)
 * - POST password (string)
 *
 * Session variables written on success:
 * - $_SESSION['logged_in'] : bool
 * - $_SESSION['user_id']   : string
 * - $_SESSION['ACType']    : string ("admin" / "user")
 * - $_SESSION['email']     : string
 */
if (!isset($_SESSION)) session_start();
/**
 * Loads all users from a semicolon-delimited CSV file.
 *
 * @param string $path Absolute path to Database.csv.
 * @return array<int,array<string,string>> List of associative rows.
 */
function loadUsers($path) {
    if (!file_exists($path)) return array();

    $fh = fopen($path, "r");
    if (!$fh) return array();

    $header = fgetcsv($fh, 0, ";");
    if (!$header) { fclose($fh); return array(); }

    $users = array();

    while (($row = fgetcsv($fh, 0, ";")) !== false) {
        if (count($row) !== count($header)) continue;
        $users[] = array_combine($header, $row);
    }

    fclose($fh);
    return $users;
}

/**
 * Finds a user row by email (case-insensitive).
 *
 * @param array<int,array<string,string>> $users Loaded users.
 * @param string $email Email to search.
 * @return array<string,string>|null Matching user row or null.
 */
function findUserByEmail($users, $email) {
    foreach ($users as $u) {
        if (isset($u["email"]) && strcasecmp($u["email"], $email) === 0) {
            return $u;
        }
    }
    return null;
}

/**
 * Verifies a plaintext password against a stored password hash.
 *
 * @param array<string,string>|null $user User row or null.
 * @param string $password Plaintext password supplied by the user.
 * @return bool True if the hash matches, otherwise false.
 */
function checkPassword($user, $password) {
    if ($user === null) return false;
    if (!isset($user["passwordHash"])) return false;
    return password_verify($password, $user["passwordHash"]);
}

// ---- main logic ----

$users = loadUsers(__DIR__ . "/Database.csv");

$email    = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? (string)$_POST['password'] : '';

if ($email === '' || $password === '') {
    $_SESSION['login_error'] = "Vyplňte email i heslo.";
    $_SESSION['login_old_email'] = $email;
    header("Location: login-page.php");
    exit;
}

$user = findUserByEmail($users, $email);

if (!checkPassword($user, $password)) {
    $_SESSION['login_error'] = "Email nebo heslo je nesprávné.";
    $_SESSION['login_old_email'] = $email;
    header("Location: login-page.php");
    exit;
}

// prevent session fixation
session_regenerate_id(true);

// store login state
$_SESSION['logged_in'] = true;
$_SESSION['user_id']   = $user['id'];
$_SESSION['ACType']    = isset($user['ACType']) ? $user['ACType'] : 'user';
$_SESSION['email']     = $user['email'];

// clear old error data
unset($_SESSION['login_error'], $_SESSION['login_old_email']);

// Success: redirect to user's page
header("Location: person-details.php?id=" . urlencode($user['id']));
exit;
