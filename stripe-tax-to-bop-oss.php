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

        if (!isset($countries[$country])) {
            $countries[$country] = [
                'Umsatzsteuersatz'   => $entry['tax_rate'] * 100,
                'Nettobetrag'        => 0,
                'Umsatzsteuerbetrag' => 0
            ];
        }


        $countries[$country]['Nettobetrag'] += $entry['filing_total_taxable_sales'];
        $countries[$country]['Umsatzsteuerbetrag'] += $entry['filing_total_tax_collected'];


    }


    fwrite(STDOUT, "#v1.0\n");


    foreach ($countries as $country => $data) {

        if ($country === 'DE' || $data['Umsatzsteuerbetrag'] == 0) {
            continue;
        }

        fwrite(STDOUT, '1,' . $country . "\n");
        fwrite(STDOUT, sprintf("2,%s,STANDARD,%s,%s,%s\n",
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