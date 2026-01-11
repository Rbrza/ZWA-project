<?php
/**
 * Shared site header (partial).
 *
 * Include this file with require_once from your pages to render the same navigation header everywhere.
 * It ensures an active session so it can show the current user's email and profile link when logged in.
 *
 * Variables used from session:
 * - $_SESSION['user_id'] : current user id (used to build profile URL)
 * - $_SESSION['email']   : current user email (displayed in header if logged in)
 *
 * Output:
 * - Emits HTML <header> markup (and a print stylesheet link).
 *
 * Notes:
 * - Because this file outputs HTML, include it only after any redirects are resolved.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<head>
    <link rel="stylesheet" href="print.css" media="print">
</head>
<header class="header">
    <ul class="header-links">
        <li>
            <a href="index.php">Osoby</a>
            <a href="insurance-list.php">Upravovat má pojištění</a>
        </li>
        <li>
            <a href="person-details.php?id=<?= urlencode(isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '') ?>">
                <i class="bi bi-person-circle profile-icon"></i>
                <?php if (isset($_SESSION['email'])): ?>
                    <span><?= htmlspecialchars($_SESSION['email']) ?></span>
                <?php else: ?>
                    <span>Přihlásit se</span>
                <?php endif; ?>
            </a>
        </li>
    </ul>
</header>
