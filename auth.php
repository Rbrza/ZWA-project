<?php
/**
 * @file auth.php
 * @brief Přístupová brána (authentication guard) pro přihlášené uživatele.
 *
 * Tento soubor se includuje na začátku všech stránek, které vyžadují
 * přihlášeného uživatele.
 *
 * Pokud uživatel není přihlášen (`$_SESSION['logged_in'] !== true`),
 * provede se HTTP přesměrování na `login-page.php` a skript je ukončen.
 *
 * ### Vedlejší efekty
 * - Spouští nebo obnovuje PHP session pomocí `session_start()`.
 * - Odesílá HTTP hlavičku `Location` při nepřihlášeném přístupu.
 *
 * ### Bezpečnost
 * - Předpokládá, že login-handler nastaví `$_SESSION['logged_in'] = true` při úspěšném přihlášení.
 * - Tento soubor musí být includován **před jakýmkoliv HTML výstupem**,
 *   jinak by `header()` způsobil chybu „headers already sent“.
 *
 * @see login-handler.php
 * @see login-page.php
 */

session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login-page.php");
    exit;
}
