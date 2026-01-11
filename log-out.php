<?php
/**
 * Logout endpoint (POST recommended).
 *
 * Destroys the current session so the user is fully logged out, including:
 * - Clearing all session variables
 * - Removing the session cookie (if cookies are used for sessions)
 * - Destroying the session data on the server
 *
 * Finally redirects the user back to the login page (PRG-friendly).
 *
 * Side effects:
 * - Sends Set-Cookie header (to expire the session cookie)
 * - Sends Location header redirect
 *
 * Security notes:
 * - Should be triggered by a POST form to avoid accidental logout from crawlers/link previews.
 * - Must run before any HTML output.
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
