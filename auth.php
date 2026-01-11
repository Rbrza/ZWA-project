<?php
/**
 * Authentication guard.
 *
 * Include this file at the top of any page that requires the user to be logged in.
 * If the user is not authenticated, they are redirected to login-page.php.
 *
 * Side effects:
 * - Starts/continues the PHP session via session_start().
 * - Sends an HTTP redirect header on unauthenticated access.
 *
 * Security notes:
 * - This file assumes your login handler sets $_SESSION['logged_in'] === true on success.
 * - Always include this BEFORE any HTML output to avoid "headers already sent".
 */
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login-page.php");
    exit;
}