These files are used by the script to process additional data external to the raw xml (codelists, etc)

Some o fthis stuff needs to be processed manually every now and again:

publisher_mapping..
dac_codelists...
are both csv files taken directly from spreadsheets

country_lang_map
is generated in the /Country_Lang_Mapping directory. 
See the README in that dir for more info.

test_meta_new.json is a json file of the test requirements. This is 
controlled by a spreadsheet, so we have a routine to generate the json
from that spreadsheet.
I think there is a bit of manual work to be done as well, but it is 
generated from the script in /Transparency_test_to_csv.
Use v2
