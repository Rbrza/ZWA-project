<?php
/**
 * Insurance catalog.
 *
 * Defines the set of insurance products available in the application.
 * Each insurance has:
 * - label: human readable name (Czech)
 * - price: monthly price (integer CZK)
 *
 * Also provides helper functions for formatting stored insurance codes.
 *
 * This file is intended to be included with require_once from PHP endpoints/pages that need the catalog.
 */
$INSURANCE_CATALOG = array(
    "nemovitost" => array("label" => "Nemovitostní", "price" => 250),
    "zivotni" => array("label" => "Životní", "price" => 199),
    "zdravotni" => array("label" => "Zdravotní", "price" => 149),
    "povinne" => array("label" => "Povinné ručení", "price" => 320),
    "zvirata" => array("label" => "Pojištění zvířat", "price" => 180),
);
/**
 * Converts a comma-separated list of insurance codes into a human readable label list.
 *
 * Example:
 *  - Input:  "nemovitost,zivotni"
 *  - Output: "Nemovitostní, Životní"
 *
 * Unknown codes are ignored (not shown).
 *
 * @param string $activeCodes Comma-separated insurance codes stored in Database.csv.
 * @param array  $catalog     Insurance catalog array (typically $INSURANCE_CATALOG).
 * @return string Comma-separated label list suitable for UI display.
 */
function renderInsuranceNames($activeCodes, $catalog) {
    if (!$activeCodes) return "";

    $out = array();
    foreach (explode(',', $activeCodes) as $code) {
        $code = trim($code);
        if ($code !== '' && isset($catalog[$code])) {
            $out[] = $catalog[$code]['label'];
        }
    }

    return implode(', ', $out);
}
