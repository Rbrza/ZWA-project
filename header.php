<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
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
