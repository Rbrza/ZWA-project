<?php
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

function findUserByEmail($users, $email) {
    foreach ($users as $u) {
        if (isset($u["email"]) && strcasecmp($u["email"], $email) === 0) {
            return $u;
        }
    }
    return null;
}

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
    exit("Missing email or password.");
}

$user = findUserByEmail($users, $email);

if (!checkPassword($user, $password)) {
    // You can redirect back with an error instead
    exit("Invalid email or password.");
}

session_start();

// prevent session fixation
session_regenerate_id(true);

// store login state
$_SESSION['logged_in'] = true;
$_SESSION['user_id']   = $user['id'];
$_SESSION['ACType']    = $user['ACType'];
$_SESSION['email']     = $user['email']; // optional

// Success: redirect to an about page.
header("Location: person-details.php?id=" . urlencode($user['id']));
exit;
