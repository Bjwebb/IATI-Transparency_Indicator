IATI Transparency Tests
=======================
Licence
-------
This file is part of IATI Transparency Tests.

Copyright 2012 caprenter <caprenter@gmail.com>

IATI Transparency Tests is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

IATI Transparency Tests is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with IATI Transparency Tests.  If not, see <http://www.gnu.org/licenses/>.


`functions/pretty_jsopn.php` is taken from a comment on php.net so no claims about copyright are made.

Getting Started
===============


Checkout the scripts
--------------------

Get some data:
You can fetch data from the IATI Registry (iatiregistry.org).  
Store each publishers data in it's own directory, which is itself inside a 'data' directory.  
e.g.  
`raw_IATI_xml_data/`  
`raw_IATI_xml_data/dfid`    
`raw_IATI_xml_data/publisher_name`          
and so on.

Settings
--------
Copy `example.settings.php` to `settings.php` and then edit the options.  
Create your `results` directory to store your results in.

Run the scripts
---------------

Using `php_cli`  e.g.
`php transparency_tests.php`

Functional Notes
----------------
Run the tests in the following order:
* `transparency_test.php` - will generate a json file of data giving counts, scores and percentages, etc for a data publisher against their IATI data files.
* `format_overview_Sig_Other_All.php` - generates some total information for all providers grouped in Signatories/Other/All. This data is required by:
* `format_all_results_v2.php` - generates 2 csv files /csv/Signatories.csv and /csv/Other.csv
* `transaprency_test_additional.sh` - generates the results for the 'top 4' tests that are a bit more complicated
N.B. There is one more test required to run the full suite. This is a test on the file updates based on the CKAN registry records.
Note to self. This is run from the `aidinfo-data/backend/run-daily/history*` scripts - not in this code base yet.
history-maker.php grabs the data
history-assessment.php makes the judgement on how often data is updated.

/contrib
--------
This directory contains a number of one off scripts that can be run by `php_cli` and should be output to csv.
Move the script into the root directory first.

Out dated formats:
------------------
* `format_results.php` - will generate a csv file of results only, grouped into data publisher types (e.g. signatory, NGO's etc)

 
