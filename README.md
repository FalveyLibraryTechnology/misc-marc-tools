# misc-marc-tools
Tools for MARC record processing used at Falvey Memorial Library

## Tool Summary

### extract_oclc.php

Given an input file of MARC records with OCLC numbers in the 001 fields, write out a text file of OCLC numbers.

### extract_oclc_and_split.php

Same as extract_oclc.php, but prefix the output filename using the hostname found in the 856 field (to sort
files by provider in preparation for running oclc_to_bib.php).

### marcfilter.php

Filter unwanted records from a MARC file (developed as part of a collection review project; currently filtering
criteria are hard-coded into the logic, but this could eventually be generalized for greater flexibility).

### oclc_to_bib.php

Given a file of OCLC numbers (such as the output of one of the extract_oclc scripts above), connect to a VuFind
instance and retrieve associated bibliographic IDs, while also confirming that retrieved records contain an
expected text string. Requires a solr.ini file to specify the location of VuFind's Solr instance -- see the
solr.ini.example file.

## Usage

Run `composer install` to load dependencies, then run the individual scripts with PHP. Scripts will give usage
notes if executed without parameters.