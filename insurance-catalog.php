<?php

$INSURANCE_CATALOG = array(
    "nemovitost" => array("label" => "Nemovitostní", "price" => 250),
    "zivotni" => array("label" => "Životní", "price" => 199),
    "zdravotni" => array("label" => "Zdravotní", "price" => 149),
    "povinne" => array("label" => "Povinné ručení", "price" => 320),
    "zvirata" => array("label" => "Pojištění zvířat", "price" => 180),
);

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
