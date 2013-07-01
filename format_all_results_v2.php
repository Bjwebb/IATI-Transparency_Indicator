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

/* This file generates 2 csv files:
 * csv/Other.csv
 * csv/Signatories.csv
 * 
 * These are the transparency results as we want them from all of the 'easy tests'
 * It should be run after format_overview_results_Sig_Other_All.php
 */
 
$tests = array ( "2.1",
                  "2.2",
                  "2.3",
                  "2.4",
                  "2.5",
                  "2.6",
                  "3.1.1",
                  "3.1.2",
                  "3.2",
                  "5.1",
                  "5.2",
                  //"5.3",
                  "6.1",
                  "6.2",
                  "6.3.1",
                  "6.3.2"
                );
$dir= "results/"; //file of json results - one for each publisher
//$data = array(); // a place to generate our csv data

//Get some data from files
//A mapping of publishers ckan id, title, and group (e.g. signatories,NGO, etc)
//$pub_data[0] =  id, //$pub_data[1] =  title, //$pub_data[2] =  group

if (($handle = fopen("helpers/publisher_mapping.csv", "r")) !== FALSE) {
    while (($pub_data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if ($pub_data[5] !=NULL) {
          $use_this_name = $pub_data[5];
        } else {
          $use_this_name = trim($pub_data[2],",");
        }
        $publishers[$pub_data[1]] = array("name"=>$use_this_name,"group"=>$pub_data[4]);
        $groups[$pub_data[1]] = $pub_data[4];
    }
    fclose($handle);
}
//print_r($publishers); die;
//$group_types=array_unique($groups);
$group_types=array("Signatory","Other");
$other = array("NGO", "NGO PLatform", "Government","Foundation","");

foreach ($group_types as $type) {
  $data = array();
  //Generate the row info
              $json = file_get_contents("helpers/tests_meta_new.json");
              $json = json_decode($json,true);
              //print_r($json); die;
              //id column
              //$data["title"][] = "id";
              //$data["totals"][] = "";
              $data["column-header"][] = "";
              $data["totals"][] = "Total Activities";
              //$data["totals"][] = "";
              
              foreach ($json['test'] as $key=>$value) {
/*
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
              }*/
                /////////////$data["test".$key][] = $key; //$value->{'Information Area'};
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
              $data["totals"][] = 1; //used to help us store totals data in a database 
              $data["title"][] = "Description";
              //$i=1;
              foreach ($json['test'] as $key=>$value) {
                
               /* if ($key == "3.1.1") {
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
                }*/
                if ($key == "2.1") {
                  $data["test1.5"][] = "1.5";
                }
                $data["test".$key][] = $value['Information Area'];
                echo $value->{'Information Area'};
                  //$i++;
              }
              //$data["test1.5"][] = "";
              $data["testGap"][] = "";
              $data["testHierarchy"][] ="Hierarchy";
              
              
              //test Number Column
              $data["title"][] = "Test";
              //$i=1;
              foreach ($json['test'] as $key=>$value) {
                
               switch ($key) {
                  case "2.1":
                    $data["test1.5"][] = "1.5";
                    $test_id = "2.1";
                    break;
                  case "3.1.1":
                    $test_id = "3.1";
                    break;
                  case "3.1.2":
                    $test_id = "3.2";
                    break;
                  case "3.2":
                    $test_id = "3.3";
                    break;
                  case "6.3.1":
                    $test_id = "6.3";
                    break;
                  case "6.3.2":
                    $test_id = "6.4";
                    break;
                  default;
                    $test_id = $key;
                    break;
                }
                 
                $data["test".$key][] = $test_id;
                //echo $value->{'Information Area'};
                  //$i++;
              }
              
              $data["testGap"][] = "";
              $data["testHierarchy"][] ="";
              
              
 //Put in aggregate data
  $data["column-header"][] = "";
  $data["column-header"][] = "Signatories";
  $data["column-header"][] = "Other";
  $data["column-header"][] = "All";
  
  // Need 3 empty columns for Hierarcy data
  for ($i=0; $i<3; $i++) {
   $data["testGap"][] = ""; 
   $data["testHierarchy"][] =""; 
  }
  //Get it first:
  //if (($handle2 = fopen("csv/test.csv", "r")) !== FALSE) {
  if (($handle2 = fopen("csv/test_Sig_all_other.csv", "r")) !== FALSE) {
    while (($results_data = fgetcsv($handle2, 1000, ",")) !== FALSE) {
      print_r($results_data);
     if  ($results_data[0] != "id" ) {
        //$data["test" . $results_data[0] ][] = $results_data[4]; //total
        // $data["test" . $results_data[0] ][] = $results_data[19]; //signatories
        //$data["test" . $results_data[0] ][] = ""; //empty column
        $data["test" . $results_data[0] ][] = $results_data[4]; //signatories
        $data["test" . $results_data[0] ][] = $results_data[7]; //other
        $data["test" . $results_data[0] ][] = $results_data[10]; //All
        if  ($results_data[0] == "2.1" ) {
          //$data["totals"][] = $results_data[2];
          //$data["totals"][] = $results_data[17];
          //$data["totals"][] = "";
          $data["totals"][] = $results_data[2];
          $data["totals"][] = $results_data[5];
          $data["totals"][] = $results_data[8];
        }
          
      }
        
    }
    fclose($handle2);
 }
 
    //Using scandir allows us to save results in alphabetical order
    //When we build the final csv files later in the process, spreadsheets will be ordered (in part)
    //by the order in which these files are created (because readdir is used)
    //It's slightly more convenient to do the ordering step here.
    $files = scandir($dir);
    sort($files);
    //if ($handle = opendir($dir)) {
      /* This is the correct way to loop over the directory. */
      //while (false !== ($file = readdir($handle))) {
      foreach ($files as $file) {
          if ($file != "." && $file != "..") { //ignore these system files
          
            $id = substr($file,0,-5); //file name without .json - this is also the ckan id and the array keys of $pub_data
            $name = $publishers[$id]["name"]; //gives us the readable title
            if ($groups[$id] == $type || ($type == "Other" && in_array($groups[$id],$other))) { // is this publisher in the group of puyblishers we are interested in
              echo $file . PHP_EOL;
              
              
              //Result Data
              $json = file_get_contents($dir . $file);
              //echo $json; die;
              $json = json_decode($json);
              //These 3 lines exclude Providers with zero activities from the final results
              if ($json->activityCount == NULL) {
                continue;
              }
              //print_r($results); die;
              //$name = substr($file,0,-5);
              //$data["title"][] = $name;
              if (isset($name)) {
                $data["column-header"][] = $name;
              } else {
                $data["column-header"][] = $id;
              }
             // $data["title"][] = "Score";
              $data["totals"][] = $json->activityCount;
             // $data["totals"][] = "";
            //  $data["test1.1"][] = "";
             // $data["test1.2"][] = "";
            //  $data["test1.3"][] = "";
            //  $data["test1.4"][] = "";
            //  $data["test2.1"][] = $json->tests->{"2.1"}->score;
             // $data["test2.2"][] = $json->tests->{"2.2"}->score;
            //  $data["test2.3"][] = $json->tests->{"2.3"}->score;
             // $data["test2.4"][] = $json->tests->{"2.4"}->score;
            //  $data["test2.5"][] = $json->tests->{"2.5"}->score;
            //  $data["test2.6"][] = $json->tests->{"2.6"}->score;
            //  $data["test3.1.1"][] = $json->tests->{"3.1.1"}->score;
           //   $data["test3.1.2"][] = $json->tests->{"3.1.2"}->score;
           //   $data["test3.2"][] = $json->tests->{"3.2"}->score;
           //   $data["test12"][] = "";
           //   $data["test13"][] = "";
           //   $data["test5.1"][] = $json->tests->{"5.1"}->score;
           //   $data["test5.2"][] = $json->tests->{"5.2"}->score;
           //   $data["test5.3"][] = $json->tests->{"5.3"}->score;
           //   $data["test6.1"][] = $json->tests->{"6.1"}->score;
           //   $data["test6.2"][] = $json->tests->{"6.2"}->score;
           //   $data["test6.3.1"][] = $json->tests->{"6.3.1"}->score;
           //   $data["test6.3.2"][] = $json->tests->{"6.3.2"}->score;
              
              
           //   $data["title"][] = $id;
              
              $data["test1.1"][] = "";
              $data["test1.2"][] = "";
              $data["test1.3"][] = "";
              $data["test1.4"][] = "";
              $data["test1.5"][] = "";
              $data["test2.1"][] = $json->tests->{"2.1"}->percentage;
              $data["test2.2"][] = $json->tests->{"2.2"}->percentage;
              $data["test2.3"][] = $json->tests->{"2.3"}->percentage;
              $data["test2.4"][] = $json->tests->{"2.4"}->percentage;
              $data["test2.5"][] = $json->tests->{"2.5"}->percentage;
              $data["test2.6"][] = $json->tests->{"2.6"}->percentage;
              $data["test3.1.1"][] = $json->tests->{"3.1.1"}->percentage;
              $data["test3.1.2"][] = $json->tests->{"3.1.2"}->percentage;
              $data["test3.2"][] = $json->tests->{"3.2"}->percentage;
              $data["test4.1"][] = "";
              $data["test4.2"][] = "";
              $data["test5.1"][] = $json->tests->{"5.1"}->percentage;
              $data["test5.2"][] = $json->tests->{"5.2"}->percentage;
              $data["test5.3"][] = $json->tests->{"5.3"}->percentage;
              $data["test6.1"][] = $json->tests->{"6.1"}->percentage;
              $data["test6.2"][] = $json->tests->{"6.2"}->percentage;
              $data["test6.3.1"][] = $json->tests->{"6.3.1"}->percentage;
              $data["test6.3.2"][] = $json->tests->{"6.3.2"}->percentage;
              $data["testGap"][] = "";
              $data["testHierarchy"][] = $json->hierarchy;
            }
          }
        }
      //}
  
  
  
//  if ($id == "theglobalfund") {
    $fp = fopen("csv/" . $type .".csv", 'w');

    foreach ($data as $fields) {
        fputcsv($fp, $fields);
    }

    fclose($fp);
  //}
}
?>
