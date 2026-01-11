<?php
/**
 * @file log-out.php
 * @brief Odhlášení uživatele (zrušení session).
 *
 * Tento skript kompletně odhlásí aktuálně přihlášeného uživatele.
 * Provede:
 *  - Vymazání všech hodnot v $_SESSION
 *  - Smazání session cookie (pokud jsou sessions vázané na cookies)
 *  - Zničení session dat na serveru
 *
 * Poté přesměruje uživatele zpět na přihlašovací stránku (`login-page.php`)
 * pomocí HTTP redirectu (POST-Redirect-GET pattern).
 *
 * ### Bezpečnost
 * - Měl by být volán přes POST formulář, aby nedošlo k nechtěnému odhlášení
 *   (např. přes otevření odkazu nebo crawler).
 * - Musí být spuštěn před jakýmkoli HTML výstupem.
 *
 * @see login-page.php
 */
session_start();

// clear all session variables
$_SESSION = array();

// remove session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destroy session
session_destroy();

// redirect to login page
header("Location: login-page.php");
exit;
