<?php
/**
 * Admin authorization guard.
 *
 * Include this file AFTER auth.php on pages that require administrator privileges.
 * Non-admin users are redirected to their own profile details page.
 *
 * Preconditions:
 * - A session has already been started (auth.php does this).
 * - $_SESSION['user_id'] contains the current user's id.
 * - $_SESSION['ACType'] contains the access type string, e.g. "admin" or "user".
 *
 * Side effects:
 * - Sends an HTTP redirect header if the user is not an admin.
 */
if ($_SESSION['ACType'] !== 'admin') {
    header("Location: person-details.php?id=" . urlencode($_SESSION['user_id']));
    exit;
}