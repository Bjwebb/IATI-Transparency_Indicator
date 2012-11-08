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

Functional Notes
----------------
* `transparency_test.php` - will generate a json file of data giving counts, scores and percentages, etc for a data publisher against their IATI data files.
* `format_results.php` - will generate a csv file of results only, grouped into data publisher types (e.g. signatory, NGO's etc)
* `settings.php` - contains some information you will need to alter in order to tell the scripts where the data is, and where the results should be stored.


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
Create your 'results' directory to store your results in.

Run the script
--------------

Using `php_cli`  
`php transparency_tests.php`
 
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

Functional Notes
----------------
* `transparency_test.php` - will generate a json file of data giving counts, scores and percentages, etc for a data publisher against their IATI data files.
* `format_results.php` - will generate a csv file of results only, grouped into data publisher types (e.g. signatory, NGO's etc)
* `settings.php` - contains some information you will need to alter in order to tell the scripts where the data is, and where the results should be stored.


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
Create your 'results' directory to store your results in.

Run the script
--------------

Using `php_cli`  
`php transparency_tests.php`
 
