<?php
/**
 * Script to extract OCLC numbers from a MARC file (assumes OCLC in 001)
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
$out = fopen($argv[2], 'w');

while ($record = $in->next()) {
    $id = $record->getFields('001')[0]->getData();
    fwrite($out, preg_replace('/[^0-9]/', '', $id) . "\n");
}

fclose($out);
