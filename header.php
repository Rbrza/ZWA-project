<?php
/**
 * @file header.php
 * @brief Sdílená hlavička webu (HTML partial).
 *
 * Tento soubor generuje jednotnou hlavičku aplikace, která se vkládá
 * do všech stránek pomocí `require_once`.
 *
 * Hlavička zobrazuje:
 * - navigační odkazy (Seznam osob, Pojištění)
 * - ikonu profilu
 * - email přihlášeného uživatele (pokud je přihlášen)
 *
 * ### Použité proměnné ze session
 * - `$_SESSION['user_id']` – ID aktuálního uživatele (pro odkaz na profil)
 * - `$_SESSION['email']` – email aktuálního uživatele (zobrazen v hlavičce)
 *
 * ### Vedlejší efekty
 * - Pokud ještě neběží session, spustí ji pomocí `session_start()`.
 * - Vypisuje HTML značky `<head>` a `<header>`.
 * - Vkládá CSS styl pro tisk (`print.css`).
 *
 * ### Poznámky k použití
 * - Tento soubor vypisuje HTML, proto musí být includován **až po**
 *   vyřešení všech přesměrování (`header("Location: ...")`).
 *
 * @see auth.php
 * @see person-details.php
 * @see index.php
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
