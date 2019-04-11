<?php

header('Content-type: text/csv');
header('Content-disposition: attachment; filename=operatori.csv');

$litere = [];
for ($i = ord('A'); $i <= ord('Z'); $i++) {
    $litere[] = chr($i);
}
$litere[] = 'etc';

$h = fopen('php://output', 'w');
fputcsv($h, ['nrCrt', 'Denumire agent economic', 'cod unic de inregistrare']);

$nrcrt = 1;
foreach ($litere as $litera) {
    foreach (parseHtml(file_get_contents(makeUrl($litera))) as $item) {

        array_unshift($item, $nrcrt++);
        fputcsv($h, $item);
    }
}
fclose($h);

function makeUrl($litera)
{
    return "https://ridis.ro/valide1.php?litera=$litera";
}

function parseHtml($html)
{
    $result = [];

    if ($html === false) {
        return $result;
    }

    libxml_use_internal_errors(true);

    $doc = new DOMDocument();
    $doc->loadHTML($html);

    $table = $doc->getElementsByTagName('table')->item(1);
    if (!$table) {
        return $result;
    }
    for ($i = 2; $i < $table->childNodes->length; $i++) {
        $tr = $table->childNodes->item($i);
        if (!$tr || strtolower($tr->nodeName) !== 'tr') {
            continue;
        }
        $nrcrt = $tr->childNodes->item(0)->textContent;
        if(!is_numeric($nrcrt)){
            continue;
        }
        $denumire = $tr->childNodes->item(1)->textContent;
        $cod = $tr->childNodes->item(2)->textContent;
        if (!$cod) {
            continue;
        }
        $result[] = [$denumire, $cod];
    }
    return $result;
}

function csvline($items)
{
    print_r($items);
}