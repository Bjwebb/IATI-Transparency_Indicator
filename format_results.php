<?php
/*
 * This file is part of IATI Transparency Tests.
 *
 * Copyright 2012 caprenter <caprenter@gmail.com>
 * 
 * IATI Transparency Tests is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * IATI Transparency Tests is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with IATI Transparency Tests.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * functions/pretty_jsopn.php is taken from a comment on php.net so no claims about copyright are made.
 */


$dir= "results/"; //file of json results - one for each publisher
//$data = array(); // a place to generate our csv data

//Get some data from files
//A mapping of publishers ckan id, title, and group (e.g. signatories,NGO, etc)
//$pub_data[0] =  id, //$pub_data[1] =  title, //$pub_data[2] =  group

if (($handle = fopen("helpers/publisher_mapping.csv", "r")) !== FALSE) {
    while (($pub_data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $publishers[$pub_data[0]] = array("name"=>trim($pub_data[1],","),"group"=>$pub_data[2]);
        $groups[$pub_data[0]] = $pub_data[2];
    }
    fclose($handle);
}
//print_r($publishers); die;
$group_types=array_unique($groups);

foreach ($group_types as $type) {
  $data = array();
  //Generate the row info
              $json = file_get_contents("helpers/tests_meta_new.json");
              $json = json_decode($json,true);
              //print_r($json); die;
              //id column
              $data["title"][] = "id";
              foreach ($json['test'] as $key=>$value) {

              if ($key == "3.1.1") {
                $key = "3.1";
              }
               if ($key == "3.1.2") {
                continue;
              }
               if ($key == "6.3.2") {
                continue;
              }
              if ($key == "6.3.1") {
                $key = "6.3";
              }
                $data["test".$key][] = $key; //$value->{'Information Area'};
                //$i++;
              }
              /*$i=1;
              foreach ($json['test'] as $test) {
                if (strlen($test["id"]) > 3) { //Allows us to combine subtests into one cell
                  $data["test".$test["id"]][] = $test["id"];
                  //$i++;
                }
              }*/
              //Description Column
              $data["title"][] = "Description";
              //$i=1;
              foreach ($json['test'] as $key=>$value) {
                
                if ($key == "3.1.1") {
                  $key = "3.1";
                }
                 if ($key == "3.1.2") {
                  continue;
                }
                 if ($key == "6.3.2") {
                  continue;
                }
                if ($key == "6.3.1") {
                  $key = "6.3";
                }
                $data["test".$key][] = $value['Information Area'];
                echo $value->{'Information Area'};
                  //$i++;
              }
  //$files = scandir($dir);
    if ($handle = opendir($dir)) {
      /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
          if ($file != "." && $file != "..") { //ignore these system files
          
            $id = substr($file,0,-5); //file name without .json - this is also the ckan id and the array keys of $pub_data
            $name = $publishers[$id]["name"]; //gives us the readable title
            if ($groups[$id] == $type) {
              echo $file . PHP_EOL;
              
              
              //Result Data
              $json = file_get_contents($dir . $file);
              //echo $json; die;
              $json = json_decode($json);
              //print_r($results); die;
              //$name = substr($file,0,-5);
              $data["title"][] = $name;
              $data["test1.1"][] = "";
              $data["test1.2"][] = "";
              $data["test1.3"][] = "";
              $data["test1.4"][] = "";
              $data["test2.1"][] = $json->tests->{"2.1"}->score;
              $data["test2.2"][] = $json->tests->{"2.2"}->score;
              $data["test2.3"][] = $json->tests->{"2.3"}->score;
              $data["test2.4"][] = $json->tests->{"2.4"}->score;
              $data["test2.5"][] = $json->tests->{"2.5"}->score;
              $data["test2.6"][] = $json->tests->{"2.6"}->score;
              $data["test3.1"][] = $json->tests->{"3.1.1"}->score + $json->tests->{"3.1.2"}->score;
              $data["test3.2"][] = $json->tests->{"3.2"}->score;
              $data["test12"][] = "";
              $data["test13"][] = "";
              $data["test5.1"][] = $json->tests->{"5.1"}->score;
              $data["test5.2"][] = $json->tests->{"5.2"}->score;
              $data["test5.3"][] = $json->tests->{"5.3"}->score;
              $data["test6.1"][] = $json->tests->{"6.1"}->score;
              $data["test6.2"][] = $json->tests->{"6.2"}->score;
              $data["test6.3"][] = $json->tests->{"6.3.1"}->score + $json->tests->{"6.3.2"}->score;
              /*$data["test4"][] = $json->tests->iatiIdentifier->score;
              $data["test5"][] = $json->tests->language->score;
              $data["test6"][] = $json->tests->activityDateStart->score;
              $data["test7"][] = $json->tests->activityDateEnd->score;
              $data["test8"][] = $json->tests->participatingOrgImplementing->score;
              $data["test9"][] = $json->tests->participatingOrgAccountable->score;
              $data["test10"][] = $json->tests->locationText->score + $json->tests->locationStructure->score;
              $data["test11"][] = $json->tests->sector->score;
              $data["test12"][] = $json->tests->budget->score;
              $data["test13"][] = "";
              $data["test14"][] = "";
              $data["test15"][] = $json->tests->transactionTypeCommitment->score;
              $data["test16"][] = $json->tests->transactionTypeDisbursementExpenditure->score;
              $data["test17"][] = $json->tests->transactionTracability->score;
              $data["test18"][] = $json->tests->documentLink->score;
              $data["test19"][] = $json->tests->conditions->score;
              $data["test20"][] = $json->tests->result->score;*/
            }
          }
      }
  }

  $fp = fopen("csv/" . $type . '.csv', 'w');

  foreach ($data as $fields) {
      fputcsv($fp, $fields);
  }

  fclose($fp);
}
?>
