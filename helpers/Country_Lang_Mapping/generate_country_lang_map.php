<?php
/*
 * This file is part of Foobar.
 * 
 * Foobar is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * Foobar is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */

/* http://download.geonames.org/export/dump/countryInfo.txt
*/

$countries = $mapped_data = array();
if (($handle = fopen("all.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $languages = $data[2];
        $languages = explode(",",$languages);
        foreach ($languages as $language) {
          if (substr($language,0,2) == "fr") {
            $lang = "fr";
            break;
          } elseif (substr($language,0,2) == "es") {
            $lang = "es";
            break;
          } elseif (substr($language,0,2) == "en") {
            $lang = "en";
            break;
          } elseif (substr($language,0,2) == "pt") {
            $lang = "pt";
            break;
          } else {
            $lang = "other";
            break;
          }
          
        }
        $countries[] = array($data[0],trim(strtolower($data[1])),$lang);
    }
    fclose($handle);
}
print_r($countries);
foreach ($countries as $country) {
  echo $country[0] . "," . $country[1] . "," . $country[2] . PHP_EOL;
}


die;







//Map each country to language
/*
 * * 
 * http://en.wikipedia.org/wiki/List_of_countries_where_English_is_an_official_language
 * http://en.wikipedia.org/wiki/List_of_countries_where_French_is_an_official_language
 * http://en.wikipedia.org/wiki/List_of_countries_where_Spanish_is_an_official_language
 * http://en.wikipedia.org/wiki/List_of_countries_where_Portuguese_is_an_official_language
 */
if (($handle = fopen("es.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $spanish[] = trim(strtolower($data[0]));
    }
    fclose($handle);
}
if (($handle = fopen("fr.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $french[] = trim(strtolower($data[0]));
    }
    fclose($handle);
}
if (($handle = fopen("en.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $english[] = trim(strtolower($data[0]));
    }
    fclose($handle);
}
if (($handle = fopen("pt.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $port[] = trim(strtolower($data[0]));
    }
    fclose($handle);
}
//print_r($spanish);
map_country_to_lang ($spanish);
map_country_to_lang ($english); 
map_country_to_lang ($port);
map_country_to_lang ($french);
function map_country_to_lang ($data_set) {
  global $countries;
  global $mapped_data;
  
  foreach ($data_set as $country) {
    if (in_array($country,$countries)) {
      echo $country . PHP_EOL;
      $key = array_search($country, $countries); 
      $mapped_data[] = array($key,$country,'es');
    }
  }
}
print_r($mapped_data);
echo count($mapped_data) . PHP_EOL;
echo count($countries) . PHP_EOL;
?>
