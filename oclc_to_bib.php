<?php
/**
 * Bib number lookup script for eBook deaccessioning process.
 *
 * Given a file of OCLC numbers extracted from a "deleted records" MARC file,
 * this script uses VuFind to obtain a bib number and to check for the presence
 * of target text indicating that the bib number should be deleted.
 *
 * The script sends important messages to stdout in addition to creating an
 * output file of bib IDs that can be deleted. Recommended usage is to
 * redirect the output of the script to a log file so that messages can be
 * read and problems investigated manually.
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

define('DEFAULT_SOLR', 'http://localhost:8080/solr/biblio/select');
define('DEFAULT_TARGET_TEXT', 'JSTOR Electronic access restricted to Villanova University patrons.');

if (!isset($argv[2])) {
    die("Usage: {$argv[0]} [input file] [output file] [target text (optional)]\n");
}

$in = array_map('trim', file($argv[1]));
$out = fopen($argv[2], 'w');
$targetText = $argv[3] ?? DEFAULT_TARGET_TEXT;
$config = parse_ini_file(__DIR__ . '/solr.ini');
$solr = $config['query_url'] ?? DEFAULT_SOLR;

foreach ($in as $oclc) {
    if ($bib = getBib($oclc, $targetText, $solr)) {
        fputs($out, $bib . "\n");
    }
}

fclose($out);

function getBib($oclc, $targetText, $solr)
{
    $query = 'oclc_num:' . $oclc;
    $request = $solr . '?q=' . urlencode($query) . '&wt=json&fl=id,collection,spelling';
    $response = json_decode(file_get_contents($request));
    if ($response->response->numFound === 0) {
        echo "$oclc: No match.\n";
        return false;
    }
    if ($response->response->numFound > 1) {
        echo "$oclc: Too many matches.\n";
        return false;
    }
    $record = $response->response->docs[0];
    $collection = $record->collection;
    if (count($collection) != 1 || $collection[0] != 'Online') {
        echo "$oclc: Unexpected collection: {$collection[0]}.\n";
        return false;
    }
    $spelling = $record->spelling;
    $foundMatch = false;
    foreach ($spelling as $line) {
        if (false !== strstr($line, $targetText)) {
            $foundMatch = true;
            break;
        }
    }
    if (!$foundMatch) {
        echo "$oclc: Missing expected phrase: " . $targetText . "\n";
        return false;
    }
    $bib = $record->id;
    echo "$oclc: Matched bib $bib\n";
    return $bib;
}

