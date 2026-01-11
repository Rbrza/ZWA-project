<?php
/**
 * @file insurance-catalog.php
 * @brief Katalog dostupných pojištění.
 *
 * Tento soubor definuje všechna pojištění, která aplikace nabízí.
 * Slouží jako centrální zdroj pravdy pro:
 * - názvy pojištění
 * - jejich měsíční ceny
 *
 * Katalog je uložen v globální proměnné `$INSURANCE_CATALOG`
 * a používá se například při:
 * - výpisu aktivních pojištění uživatele
 * - výpočtu měsíčního poplatku (MT)
 * - zobrazení názvů pojištění v UI
 *
 * Soubor také obsahuje pomocnou funkci pro převod kódů pojištění
 * uložených v CSV na čitelné názvy.
 *
 * @see get-user.php
 * @see update-insurance.php
 * @see person-details.php
 */

/**
 * Katalog všech dostupných pojištění.
 *
 * Klíčem pole je technický kód pojištění (uložený v Database.csv).
 * Hodnota obsahuje:
 * - label: název pojištění pro zobrazení
 * - price: měsíční cena v CZK
 *
 * @var array<string,array{label:string,price:int}>
 */
$INSURANCE_CATALOG = array(
    "nemovitost" => array("label" => "Nemovitostní", "price" => 250),
    "zivotni"    => array("label" => "Životní", "price" => 199),
    "zdravotni"  => array("label" => "Zdravotní", "price" => 149),
    "povinne"    => array("label" => "Povinné ručení", "price" => 320),
    "zvirata"    => array("label" => "Pojištění zvířat", "price" => 180),
);

/**
 * Převede seznam kódů pojištění na čitelné názvy.
 *
 * V databázi jsou pojištění uložena jako:
 *
 *     nemovitost,zivotni,zvirata
 *
 * Tato funkce je převede na:
 *
 *     Nemovitostní, Životní, Pojištění zvířat
 *
 * Neznámé kódy jsou ignorovány.
 *
 * @param string $activeCodes Čárkou oddělený seznam kódů pojištění z Database.csv.
 * @param array  $catalog     Katalog pojištění (typicky `$INSURANCE_CATALOG`).
 *
 * @return string Seznam názvů pojištění oddělených čárkou.
 */
function renderInsuranceNames($activeCodes, $catalog)
{
    if (!$activeCodes) {
        return "";
    }

    $out = array();

    foreach (explode(',', $activeCodes) as $code) {
        $code = trim($code);
        if ($code !== '' && isset($catalog[$code])) {
            $out[] = $catalog[$code]['label'];
        }
    }

    return implode(', ', $out);
}
