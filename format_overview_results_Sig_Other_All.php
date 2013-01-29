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

/* This file generates aggregate pass/fail scores for the transaparency 
 * tests. It is required by format_all_results_v2.php to show overall
 * scores against the groups of results (signatories/Other)
 * 
 * This should be run before format_all_results_v2.php
 */


//We can't perform all tests, and as this is about producing a csv file specifying which ones we want to report on here helps.
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
                  //"5.3", //the only test agianst a number of transactions, not activities
                  "6.1",
                  "6.2",
                  "6.3.1",
                  "6.3.2"
                );
//We are going to want to know how many activities were tested and how many passed each test both over all and grouped into type of publisher
$sum_all_activities = 0;
$sum_all_passes = array();

//We use these variables to store totals for each publisher type
$total_activities = array();
$total_passes = array();
$percentage = NULL;

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
$group_types=array_unique($groups); // An array of all possible publisher types
//print_r ($group_types); die;
//OVERRIDE - I think the line above is now obscelete
//Instead we only want 2 group types - Signatory and Other
$group_types=array("Signatory","Other");
$other = array("NGO", "NGO PLatform", "Government","Foundation"); //this specifies which classifications should be re-classified as 'Other'

$data = array(); //We store our csv data in here for conversion later

//Generate the column/row info
$json = file_get_contents("helpers/tests_meta_new.json"); //this file has all the info about the tests in it
$json = json_decode($json,true);
//print_r($json); die;

//id column
$data["title"][] = "id"; //Sets the column header
//Set the row values for the id column
foreach ($json['test'] as $key=>$value) {
  //Special Cases
  /*if ($key == "3.1.1") {
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
  //Store the ID's as row values
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
$data["title"][] = "Description"; //Column header
//$i=1;

//Set the rows for the Description column
foreach ($json['test'] as $key=>$value) {
  
  /*if ($key == "3.1.1") {
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
  $data["test".$key][] = $value['Information Area'];
  //echo $value->{'Information Area'};
    //$i++;
}


//Run through all of our results files to aggregate the data.
//We do this one publisher type at a time
//$files = scandir($dir);
foreach ($group_types as $type) {
  //Need to add 3 columns to the csv for each group
  $data["title"][] = "Tested (" . $type  . ")";
  $data["title"][] = "Passed (" . $type  . ")";
  $data["title"][] = "% (" . $type  . ")";
  
  //Now loop through all the results for this type of publisher and generate 3 totals for the 3 columns
  if ($handle = opendir($dir)) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
        
          $id = substr($file,0,-5); //file name without .json - this is also the ckan id and the array keys of $pub_data
          $name = $publishers[$id]["name"]; //gives us the readable title
          //if ($groups[$id] == $type) {
          if ($groups[$id] == $type || ($type == "Other" && in_array($groups[$id],$other))) { // is this publisher in the group of puyblishers we are interested in
            //echo $file . PHP_EOL;
            
            
            //Result Data
            $json = file_get_contents($dir . $file);
            //echo $json; die;
            $json = json_decode($json);
            //print_r($results); die;
            //$name = substr($file,0,-5);
            
            $total_activities[$type] += $json->activityCount;
            foreach ($tests as $test) { //restrict the calculation to the tests we allow/set at the top of this file
              $total_passes[$type][$test] += $json->tests->{$test}->count;
            }
          }
        }
      }
    } //end loop through all files for this type
}
     
//print_r($total_activities); die;
//Now store the data
$all_keys = array_keys($data); //Sets up an array of all the test e.g. test 1.1, test1.2 etc
unset ($all_keys[0]); //we don't need the 'title' value
//print_r($all_keys); die;

//loop through a group at a time
foreach ($group_types as $type) {
  $sum_all_activities += $total_activities[$type]; //Total activities tested for all groups combined. Save for use later
  //For each test basically
  foreach ($all_keys as $key) {
    $test_id = substr($key,4); //e.g. turn test1.1 into 1.1 - silly but a mapping seems easiest way to do this
   
    if (in_array($test_id, $tests)) {
      $data[$key][] = $total_activities[$type];
      $data[$key][] = $total_passes[$type][$test_id];
      $data[$key][] = 100 * round($total_passes[$type][$test_id]/$total_activities[$type],2);
      //$sum_all_activities += $total_activities[$type];
      $sum_all_passes[$test_id] += $total_passes[$type][$test_id]; //Store all passes for all activities for use later
    } else { //print blank cells
      $data[$key][] = "";
      $data[$key][] = "";
      $data[$key][] = "";
    }
  }
}

//This adds the totals for all activities against all tests to the end of the file
$data["title"][] = "Tested (ALL)";
  $data["title"][] = "Passed (ALL)";
  $data["title"][] = "% (ALL)";
foreach ($all_keys as $key) {
  $test_id = substr($key,4); //e.g. turn test1.1 into 1.1
  if (in_array($test_id, $tests)) {
    $data[$key][] = $sum_all_activities;
    $data[$key][] =  $sum_all_passes[$test_id];
    $data[$key][] = 100 * round( $sum_all_passes[$test_id]/$sum_all_activities,2);
  } else {
    $data[$key][] = "";
    $data[$key][] = "";
    $data[$key][] = "";
  }
}
     
     
     

//Write the data to csv
//print_r($data);
$filename = "test_Sig_all_other";
$fp = fopen("csv/" . $filename . '.csv', 'w');

foreach ($data as $fields) {
    fputcsv($fp, $fields);
}

fclose($fp);

?>
