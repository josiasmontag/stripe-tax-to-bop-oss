<?php

define("NAME", basename(array_shift($argv)));

if (count($argv)) {

    $entries = [];

    foreach ($argv as $filename) {
        if (($path = realpath($filename)) === false) {
            fwrite(STDERR, NAME . ": $filename: No such file\n");
            exit(1);
        }

        $entries = array_merge($entries, readCsv($path));
    }

    createCsv($entries);
    exit(0);

} else {
    fwrite(STDERR, usage());
    exit(1);
}

function floatvalue($val)
{
    $val = str_replace(",", ".", $val);
    $val = preg_replace('/\.(?=.*\.)/', '', $val);
    return floatval($val);
}

function readCsv($path)
{

    $file = fopen($path, 'r');
    $rows = [];
    $header = fgetcsv($file);
    while ($row = fgetcsv($file)) {
        $rows[] = array_combine($header, $row);
    }
    fclose($file);

    return $rows;
}

function createCsv($entries)
{

    $countries = [];

    foreach ($entries as $entry) {

        $country = $entry['country_code'];

        if ($country === 'GR') {
            $country = 'EL';
        }

        if ($entry['filing_currency'] !== 'eur') {
            continue;
        }

        $key = $country.($entry['tax_rate'] * 100);


        if (!isset($countries[$key])) {
            $countries[$key] = [
                'Land' => $country,
                'Umsatzsteuersatz' => $entry['tax_rate'] * 100,
                'Nettobetrag' => 0,
                'Umsatzsteuerbetrag' => 0,
            ];
        }

        $countries[$key ]['Nettobetrag'] += floatval($entry['filing_total_taxable_sales']);
        $countries[$key ]['Umsatzsteuerbetrag'] += floatval($entry['filing_total_tax_collected']);

    }

    fwrite(STDOUT, "#v2.0\n");

    krsort($countries);

    foreach ($countries as $key => $data) {

        if ($data['Land'] === 'DE' || $data['Umsatzsteuerbetrag'] == 0) {
            continue;
        }

        if($country != $data['Land']) {
            $country = $data['Land'];
            $n = 1;
            fwrite(STDOUT, $n . ',' . $country . "\n");
        }

        $n++;

        fwrite(STDOUT, sprintf("%s,%s,STANDARD,%s,%s,%s\n",
            $n,
            $country,
            number_format($data['Umsatzsteuersatz'], 2, '.', ''),
            number_format($data['Nettobetrag'], 2, '.', ''),
            number_format($data['Umsatzsteuerbetrag'], 2, '.', ''))
        );

    }

}

function usage()
{
    $out = sprintf("usage: %s [file ...]\n", NAME);

    return $out;
}
