<?php
/**
 * Script to filter MARC file based on 710 values -- skip records
 * with missing 710 or with blacklisted 710 value. Write the rest
 * to an output file. Also create an output file listing all IDs
 * that were retained.
 *
 * PHP version 7
 *
 * Copyright (C) Villanova University 2018.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category MARC
 * @package  Tools
 * @author   Demian Katz <demian.katz@villanova.edu>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/FalveyLibraryTechnology/misc-marc-tools GitHub
 */
require_once 'vendor/autoload.php';

if (!isset($argv[2])) {
    die("Usage: {$argv[0]} [input file] [output file]\n");
}

$in = new File_MARC($argv[1]);
$out = fopen($argv[2], 'wb');

$goodIds = [];

while ($record = $in->next()) {
    $id = $record->getFields('001')[0]->getData();
    $sevenTens = $record->getFields('710');
    if (count($sevenTens) == 0) {
        echo "Skipping $id -- no 710\n";
        continue;
    }
    if ($onBlacklist = blacklisted($record)) {
        echo "Skipping $id -- blacklisted value ($onBlacklist)\n";
        continue;
    }
    echo "Writing $id\n";
    $goodIds[] = $id;
    fwrite($out, $record->toRaw());
}

fclose($out);

file_put_contents($argv[2] . '.ids', implode("\n", $goodIds) . "\n");

function blacklisted($record)
{
    $values710 = [
        'Books24.*',
        'Burney Newspaper.*',
        'Early English Books.*',
        'Eighteenth Century.*',
        'Geological Survey.*',
        'George C\. Marshall.*',
        'Gerritsen Collection.*',
        'Goddard Space.*',
        'Langley Research.*',
        'Making of the Modern World.*',
        'NASA.*',
        'National Center for Education Statistics.*',
        'Sabin Americana.*',
        'Taylor & Francis EBA',
        'United States.*',
        'U\.\s*S\..*',
        '.*\(U\.\s*S\.\).*',
    ];

    $values830 = [
        'Early American Imprints.*',
    ];

    $all = [710 => $values710, 830 => $values830];

    foreach ($all as $field => $values) {
        foreach ($record->getFields($field) as $field) {
            foreach ($field->getSubfields() as $subfield) {
                foreach ($values as $value) {
                    $string = $subfield->getData();
                    if (preg_match('/' . $value . '/i', $string)) {
                        return $string;
                    }
                }
            }
        }
    }
    return false;
}
