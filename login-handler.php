<?php
/**
 * @file login-handler.php
 * @brief Zpracování přihlášení uživatele (POST).
 *
 * Tento skript ověřuje přihlašovací údaje (email + heslo) proti databázi `Database.csv`
 * a při úspěchu vytvoří autentizovanou session.
 *
 * Používá POST-Redirect-GET:
 * - při chybě uloží chybovou zprávu + vyplněný email do session (flash) a přesměruje zpět na `login-page.php`
 * - při úspěchu regeneruje session ID, uloží stav přihlášení a přesměruje na profil uživatele
 *
 * ### Vstup
 * - `POST email` (string)
 * - `POST password` (string)
 *
 * ### Session proměnné (při úspěchu)
 * - `$_SESSION['logged_in']` (bool) – přihlášen/nepřihlášen
 * - `$_SESSION['user_id']` (string) – ID uživatele
 * - `$_SESSION['ACType']` (string) – typ účtu (`admin` / `user`)
 * - `$_SESSION['email']` (string) – email uživatele
 *
 * ### Flash proměnné (při chybě)
 * - `$_SESSION['login_error']` – text chyby pro zobrazení na login stránce
 * - `$_SESSION['login_old_email']` – předvyplnění emailu po chybě
 *
 * ### Bezpečnost
 * - Používá `password_verify()` pro ověření hesla.
 * - Používá `session_regenerate_id(true)` proti session fixation.
 * - Přesměrování probíhá před výstupem HTML (musí být bez echo/print před header()).
 *
 * @see login-page.php
 * @see auth.php
 * @see person-details.php
 */

if (!isset($_SESSION)) {
    session_start();
}

/**
 * Načte všechny uživatele ze CSV databáze.
 *
 * CSV je odděleno středníkem (`;`). První řádek je hlavička.
 * Každý další řádek se mapuje na asociativní pole (hlavička => hodnota).
 * Rozbité řádky (jiný počet sloupců) se ignorují.
 *
 * @param string $path Absolutní nebo relativní cesta k `Database.csv`.
 * @return array<int,array<string,string>> Pole uživatelů jako asociativní záznamy.
 */
function loadUsers($path)
{
    if (!file_exists($path)) {
        return array();
    }

    $fh = fopen($path, "r");
    if (!$fh) {
        return array();
    }

    $header = fgetcsv($fh, 0, ";");
    if (!$header) {
        fclose($fh);
        return array();
    }

    $users = array();

    while (($row = fgetcsv($fh, 0, ";")) !== false) {
        if (count($row) !== count($header)) {
            continue;
        }
        $users[] = array_combine($header, $row);
    }

    fclose($fh);
    return $users;
}

/**
 * Najde uživatele podle emailu (case-insensitive).
 *
 * @param array<int,array<string,string>> $users Seznam uživatelů načtený z CSV.
 * @param string $email Email, který chceme vyhledat.
 * @return array<string,string>|null Asociativní záznam uživatele, nebo null pokud neexistuje.
 */
function findUserByEmail($users, $email)
{
    foreach ($users as $u) {
        if (isset($u["email"]) && strcasecmp($u["email"], $email) === 0) {
            return $u;
        }
    }
    return null;
}

/**
 * Ověří heslo vůči uloženému hash.
 *
 * @param array<string,string>|null $user Záznam uživatele nebo null.
 * @param string $password Heslo zadané uživatelem v plaintextu.
 * @return bool True pokud heslo sedí, jinak false.
 */
function checkPassword($user, $password)
{
    if ($user === null) {
        return false;
    }
    if (!isset($user["passwordHash"])) {
        return false;
    }
    return password_verify($password, $user["passwordHash"]);
}

/* --------------------------------------------------------------------------
 * Hlavní logika
 * -------------------------------------------------------------------------- */

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

/* Ochrana proti session fixation */
session_regenerate_id(true);

/* Uložení stavu přihlášení */
$_SESSION['logged_in'] = true;
$_SESSION['user_id']   = $user['id'];
$_SESSION['ACType']    = isset($user['ACType']) ? $user['ACType'] : 'user';
$_SESSION['email']     = $user['email'];

/* Úklid flash proměnných */
unset($_SESSION['login_error'], $_SESSION['login_old_email']);

/* Přesměrování na profil */
header("Location: person-details.php?id=" . urlencode($user['id']));
exit;
