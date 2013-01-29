The Transparency Incicator.csv file is a dump from a spreadsheet generated to explain the transparency tests

The script parses the csv file and generates the test_meta_new.json file that we use to perform the tests AND to display the results so there is consistency between everything.

The prettify script doesn't seem to work all that well so put the output through:
http://jsonformatter.curiousconcept.com/

v1 is the first attempt at the tests
v2 is thew current one to use.
It generates test_meta_new_v2.json
CHECK - run a diff on anything generated with the current file in /helpers/test_meta_new.json
move this to the /helpers dir and rename it test_meta_new_json


