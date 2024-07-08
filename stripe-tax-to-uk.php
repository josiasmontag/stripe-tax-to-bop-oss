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

        if ($country !== 'GB') {
            continue;
        }

        if ($entry['filing_currency'] !== 'gbp') {
            continue;
        }

        if (!isset($countries[$country])) {
            $countries[$country] = [
                'Sales' => 0,
                'Tax' => 0,
            ];
        }

        $countries[$country]['Sales'] += floatval($entry['filing_total_sales']);
        $countries[$country]['Tax'] += floatval($entry['filing_tax_payable']);

    }

    foreach ($countries as $country => $data) {

        fwrite(STDOUT, sprintf("%s,0,%s,0,%s,%s,0,0,0\n",
            number_format($data['Tax'], 0, '.', ''),
            number_format($data['Tax'], 0, '.', ''),
            number_format($data['Tax'], 0, '.', ''),
            number_format($data['Sales'], 0, '.', ''))
        );

    }

}

function usage()
{
    $out = sprintf("usage: %s [file ...]\n", NAME);

    return $out;
}
