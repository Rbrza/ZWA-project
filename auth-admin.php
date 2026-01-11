<?php
/**
 * @file auth-admin.php
 * @brief Ověření administrátorských oprávnění uživatele.
 *
 * Tento soubor se používá na stránkách, které vyžadují administrátorský přístup.
 * Musí být includován **po** `auth.php`, protože předpokládá aktivní session
 * a přihlášeného uživatele.
 *
 * Pokud aktuální uživatel nemá oprávnění `admin`, je přesměrován
 * na svou vlastní stránku s detaily profilu.
 *
 * ### Předpoklady
 * - PHP session již běží (zajištěno pomocí `auth.php`).
 * - `$_SESSION['user_id']` obsahuje ID aktuálního uživatele.
 * - `$_SESSION['ACType']` obsahuje typ účtu (`"admin"` nebo `"user"`).
 *
 * ### Vedlejší efekty
 * - Odesílá HTTP hlavičku `Location` při nepovoleném přístupu.
 * - Ukončuje běh skriptu pomocí `exit`.
 *
 * ### Bezpečnost
 * - Zabraňuje běžným uživatelům přistupovat k administrátorským funkcím.
 * - Zajišťuje, že nelze zobrazit nebo měnit cizí účty bez oprávnění.
 *
 * @see auth.php
 * @see person-details.php
 */

if ($_SESSION['ACType'] !== 'admin') {
    header("Location: person-details.php?id=" . urlencode($_SESSION['user_id']));
    exit;
}
