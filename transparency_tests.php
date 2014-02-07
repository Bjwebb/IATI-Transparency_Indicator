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



include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
include ('functions/pretty_json.php');
$test_meta_file = "helpers/tests_meta_new.json"; // A file with our testing rules in.

//Country to Language map array
$country_map = array();
if (($handle = fopen("helpers/country_lang_map.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $country_map[$data[0]] = $data[2];
      }
}
/*$metadata_header = array("provider" => $corpus,
                        "hierarchy" => 0,
                        "activityCount" => 100,
                        "tests" => array( "activityDateStart" => array("count"=>30,"score" => 1),
                                          "activityDateEnd" => array("count"=>30,"score" => 1),
                                          ),
                        );
$json = json_encode($metadata_header); 
file_put_contents($output_file,$json);     //die;                   
*/
/*
 *  <activity-date type="start-actual" iso-date="2010-11-15"/>
 *  <activity-date type="end-planned" iso-date="2011-03-31"/>
*/


$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value
//If you've got your data in git repo from e.g. https://github.com/Bjwebb/IATI-Data-Snapshot 
if ($dirs[2] == ".git") {
  unset($dirs[2]);
}
if ($dirs[3] == "README.md") {
  unset($dirs[3]);
}
  
//print_r($dirs); die;
/* To test a subset or single dataset, put an array of dataset here:*/
//$dirs = array("acdi_cida");
//$dirs = array("concernuk"); //conditions,location
//$dirs = array("unops"); //location
//$dirs = array("dfid");
//$dirs = array("maec-dgpolde");
//$dirs = array("theglobalfund");
//$dirs = array("worldbank");
//$dirs = array("concernuk");
//$dirs = array("minbuza_nl");
//$dirs = array("akfuk73");
//$dirs = array("unitedstates");
//$exclude  = array("art19");
//$dirs = array("sida");
$exclude  = array();
foreach ($dirs as $corpus) {
    if (!in_array($corpus,$exclude)) {
        echo $corpus;
        $path_to_corpus = $dir . $corpus . "/"; //path to directory //$dir is set in settings.php
        $output_file = $output_dir . $corpus . '.json';
        //Get the existing data on this provider from a file
        //If file doesn't exist create the start of one
        if (!file_exists($output_file)) {
          $metadata_header = array( "provider" => $corpus,
                                    "hierarchy" => 0,
                                    "activityCount" => 100
                                   );
          $json = json_encode($metadata_header); 
          file_put_contents($output_file,$json);     //die; 
        }

        $json = file_get_contents($output_file);
        $metadata = json_decode($json,true);
        //print_r($metadata); //die;

        //Get the test metadata so we know what threasholds and scores to apply to this test
        $test_meta = file_get_contents($test_meta_file);
        $test_meta = json_decode($test_meta,true);
        //print_r($test_meta); die;

        //test the providers files and return the data for us
        $data = array();//attempt to make sure it's empty
        $data = count_attributes($path_to_corpus);

        //Set the hierarcy that we're going to test one. the rule is the lowest i.e. highest number which should be the last value of $data["hierarchies"]
        $metadata["hierarchy"] = end($data["hierarchies"]);
        reset($data["hierarchies"]); //end moves the array pointer to the end of the array, so lets rest it
        // Number of activities
        $metadata["activityCount"] = $data["no-activities"][$metadata["hierarchy"]]; //number of activities tested at our hierarchy level

        //get the results for the json file
        //sTet up some variables for activiy-date element first
        //$attribute_values = array("start-planned","start-actual","end-planned","end-actual");
        $attribute_values = array("start","end");
        //$attribute_start_values = array("start-planned","start-actual");
        //$attribute_end_values = array("end-planned","end-actual");
        $start = $end = 0;

        //print_r($data["participating_org_implementing"]); die;


        foreach ($data["hierarchies"] as $hierarchy) { //The count attributes routine fetches data at all hierarchies, but we only need to test the lowest usually. 
          if ($hierarchy == $metadata["hierarchy"]) { //Set for each provider in a metadata file
            //Elements
            $document_links = $result_element = $result_element_with_indicator = $conditions = 0;
            if (isset($data["document_links"][$hierarchy])) { $document_links = count($data["document_links"][$hierarchy]); }
            if (isset($data["result_element"][$hierarchy])) { $result_element = count($data["result_element"][$hierarchy]); }
            if (isset($data["result_element_with_indicator"][$hierarchy])) {
              $result_element_with_indicator = array_unique($data["result_element_with_indicator"][$hierarchy]);
              $result_element_with_indicator = count($result_element_with_indicator);
            }              
            if (isset($data["conditions"][$hierarchy])) { 
              $conditions = array_unique($data["conditions"][$hierarchy]);
              $conditions = count($conditions); 
            }
            
            //Participating org test
            $participating_org_accountable  = $participating_org_implementing = 0;
            if (isset($data["participating_org_accountable"][$hierarchy])) { 
              $participating_org_accountable  = array_unique($data["participating_org_accountable"][$hierarchy]); 
              $participating_org_accountable  = count($participating_org_accountable);
            }
            if (isset($data["participating_org_implementing"][$hierarchy])) { 
              $participating_org_implementing = array_unique($data["participating_org_implementing"][$hierarchy]); 
              $participating_org_implementing = count($participating_org_implementing);
            }
            //echo $participating_org_implementing; die;
            
            //Budget/Planned Disbursement
            $budget = 0;
            if (isset($data["budget"][$hierarchy])) { $budget = count($data["budget"][$hierarchy]); }
            
            //Identifiers
            $good_ids = 0;
            $unique_ids = array_unique($data["identifiers"][$hierarchy]["good"]); //In case identifiers are used more than once
            $good_ids_values = array_count_values($data["identifiers"][$hierarchy]["good"]); 
            /* Print duplicate iati-identifiers to the screen 
            foreach ($good_ids_values as $key=>$good_ids_value) {
              if ($good_ids_value > 1) {
                echo $key . ':' . $good_ids_value . PHP_EOL;
              }
            }
            die;
            */
            $good_ids = count($unique_ids);
            
            //Transactions 
            $transaction_type_commitment = $transaction_type_disbursements = $transaction_type_expenditure = 0;
            $unique_commitments = $unique_disbursements = $unique_expenditure = array();
            if (isset($data["transaction_type_commitment"][$hierarchy])) {
              $unique_commitments = array_unique($data["transaction_type_commitment"][$hierarchy]); //In case one activity has more than one commitment
              $transaction_type_commitment = count($unique_commitments);
            }
            
            if (isset($data["transaction_type_disbursement"][$hierarchy])) {
              $unique_disbursements = array_unique($data["transaction_type_disbursement"][$hierarchy]); //In case one activity has more than one commitment
              $transaction_type_disbursements = count($unique_disbursements);
              //echo $transaction_type_disbursements; //die;
            }
            if (isset($data["transaction_type_expenditure"][$hierarchy])) {
              $unique_expenditure = array_unique($data["transaction_type_expenditure"][$hierarchy]); //In case one activity has more than one commitment
              $transaction_type_expenditure = count($unique_expenditure);
            }
            //echo $transaction_type_expenditure; die;
            //Test to see if an activity has either
            //Both a disbursement and an expenditure
            /* This would mean looping through one array and seeing if iati-identifier values are in both. And getting a count 
             * Or if one array is smaller than the treashold then it's a fail??
             * Or an array diff would give us numbers of activites that don't have both (but not the numbers without any!!
            //OR
            //Either a disbursement or an expenditure
            * We could merge the arrays, and then array unique it to get our count
            */
            $disbursements_expenditure = array_merge($unique_disbursements,$unique_expenditure);
            $unique_disbursements_expenditure = array_unique($disbursements_expenditure);
            $disbursements_expenditure_count = count($unique_disbursements_expenditure);
            
            //Tracable Transactions If Both D and IF must pass INDIVIDUAL thresholds
            /*$tracable_IF_percent = $tracable_D_percent = 0;
            $tracable_data = array();
            $no_disbursements = $data["no_disbursements"][$hierarchy];
            $no_tracable_D_transactions =  $data["no_tracable_D_transactions"][$hierarchy];
            if ($no_tracable_D_transactions > 0 ) {  //avoid divide by zero
              $tracable_D_percent = 100 * round($no_tracable_D_transactions / $no_disbursements,4);
            }
            //echo $no_disbursements . PHP_EOL;
            $no_incoming_funds = $data["no_incoming_funds"][$hierarchy];
            $no_tracable_IF_transactions =  $data["no_tracable_IF_transactions"][$hierarchy];
            if ($no_tracable_IF_transactions > 0 ) {  //avoid divide by zero
              $tracable_IF_percent = 100 * round($no_tracable_IF_transactions / $no_disbursements,4);
            }
            if ($tracable_IF_percent > 75 && $tracable_D_percent > 75) {
              $tracable_score = 2;
            } elseif ($tracable_IF_percent > 50 && $tracable_D_percent > 50) {
              $tracable_score = 1;
            } else {
              $tracable_score = 0;
            }
            */
              
            //Tracable Transactions If Both D and IF COMBINED must pass a threshold
            $no_disbursements = $no_incoming_funds = $no_tracable_transactions = $percent_tracable = 0;
            $no_disbursements = $data["no_disbursements"][$hierarchy];
            $no_incoming_funds = $data["no_incoming_funds"][$hierarchy];
            echo $no_incoming_funds . PHP_EOL;
            $transactions_that_should_be_traced = $no_disbursements + $no_incoming_funds;
            //echo $transactions_that_should_be_traced . PHP_EOL;
            $no_tracable_transactions = $data["no_tracable_transactions"][$hierarchy];
            //echo $no_tracable_transactions . PHP_EOL;
           // die;
            if ($no_tracable_transactions > 0 ) { //avoid divide by zero
              $percent_tracable = 100 * round($no_tracable_transactions / $transactions_that_should_be_traced ,4);
              //$tracable_threashold = $test_meta["test"]["Financial transaction recipient / Provider activity Id"]["threashold"];
              //echo $test_meta["test"]["Financial transaction recipient / Provider activity Id"]["threashold"]; 
              if ($percent_tracable >= 75) {
                //echo "yes"; die;
                $tracable_score = 2;
                //$tracable_score = $test_meta["test"]["Financial transaction recipient / Provider activity Id"]["score"];
              } elseif ($percent_tracable >= 50) {
                $tracable_score = 1;
              } else {
                $tracable_score = 0;
              }
            } else {
              $tracable_score = 0;
            }
            
            
            //Location
            //$activities_with_location = $activities_with_coordinates = $activities_with_adminstrative = 0;
            //Count activities with structured location data
            //This is either co-ordinates present or
            //administartive element and adm1 or adm2 attribute
            //This MUST be a subset of the location count (mustn't it?)
            $activities_with_coordinates = $activities_with_adminstrative = array();//set up empty arrays first
            if (isset($data["activities_with_coordinates"][$hierarchy])) { 
              $activities_with_coordinates = array_unique($data["activities_with_coordinates"][$hierarchy]);
            }
            if (isset($data["activities_with_adminstrative"][$hierarchy])) { 
              $activities_with_adminstrative = array_unique($data["activities_with_coordinates"][$hierarchy]);
            }
            $activities_with_structure_locations = array_merge($activities_with_coordinates,$activities_with_adminstrative); //if arrays are empty this is ok!
            $activities_with_structure_locations = array_unique($activities_with_structure_locations); //need to unique them as activities can have both
            $activities_with_structure_locations_count = count($activities_with_structure_locations);
            //echo $activities_with_structure_locations_count . PHP_EOL;

            $activities_with_location_count = 0;
            $activities_with_location = array();
            if (isset($data["activities_with_location"][$hierarchy])) { 
              $activities_with_location =  array_unique($data["activities_with_location"][$hierarchy]); 
            }
            //$activities_with_location = array_merge($activities_with_location,$activities_with_structure_locations);
            $activities_with_location = array_unique($activities_with_location);
            $activities_with_location_count = count($activities_with_location);
            //echo $activities_with_location_count . PHP_EOL;
            
            //
            $location_level_1 = $activities_with_location_count;
            $location_level_2 = $activities_with_structure_locations_count;
              
             //die;
             
            //Sector
            $activities_sector_declared_dac = $activities_sector_assumed_dac = array();
            if (isset($data["activities_sector_declared_dac"][$hierarchy])) { 
              $activities_sector_declared_dac = $data["activities_sector_declared_dac"][$hierarchy];
              $activities_sector_declared_dac = array_unique($activities_sector_declared_dac);
            }
            if (isset($data["activities_sector_assumed_dac"][$hierarchy])) { 
              $activities_sector_assumed_dac = $data["activities_sector_assumed_dac"][$hierarchy];
              $activities_sector_assumed_dac = array_unique($activities_sector_assumed_dac);
            }
            $activities_with_dac_sector = array_merge($activities_sector_declared_dac,$activities_sector_assumed_dac); //probably don't need to 'unique' this, but won't hurt
            $activities_with_dac_sector = array_unique($activities_with_dac_sector);
            $activities_with_dac_sector_count = count($activities_with_dac_sector);
            //echo $activities_with_dac_sector_count . PHP_EOL;
           // die;
           // die;
            //Last-updated-datetime
            //$most_recent = $data["most_recent"][$hierarchy];
            
            //Activity Dates
            foreach ($attribute_values as $value) { //Loop through all possible results
              
              if (isset($data["activities_with_attribute"][$hierarchy][$value])) {
                //echo $value . PHP_EOL;
                $count = count(array_unique($data["activities_with_attribute"][$hierarchy][$value]));
                //if (in_array($value, $attribute_start_values)) {
                if ($value == "start") {
                  $start = $start + $count;
                //} elseif (in_array($value, $attribute_end_values)) {
                } else if ($value == "end") {
                  $end = $end + $count;
                }
              }
            }
            
            //Languages
            $activies_in_country_lang = $activities_with_one_recipient_country = array();
            if (isset($data["activies_in_country_lang"][$hierarchy])) {
              $activies_in_country_lang = $data["activies_in_country_lang"][$hierarchy];
              $activies_in_country_lang = array_unique($activies_in_country_lang);
            }
            if (isset($data["activities_with_one_recipient_country"][$hierarchy])) {
              $activities_with_one_recipient_country = $data["activities_with_one_recipient_country"][$hierarchy];
              //$activities_with_one_recipient_country = array_unique($activies_in_country_lang);
            }
            $activies_in_country_lang_count = count($activies_in_country_lang);
            // FIXME this is misnamed, see definition above
            $activities_with_one_recipient_country_count = count(array_unique($activities_with_one_recipient_country)); 
            //echo $activies_in_country_lang_count . PHP_EOL; //die;
            //echo $activities_with_one_recipient_country_count; die;
          } 
        }
        //echo $start . PHP_EOL;
       // echo $end . PHP_EOL;

        //Work out the scores
        $good_ids_score = get_score($good_ids,"2.1");
        $activies_in_country_lang_score = get_score($activies_in_country_lang_count,"2.2",$activities_with_one_recipient_country_count);
        $start_score = get_score($start,"2.3"); //Start Date
        $end_score = get_score($end,"2.4"); //End Date
        $participating_org_implementing_score = get_score($participating_org_implementing,"2.5");
        $participating_org_accountable_score = get_score($participating_org_accountable,"2.6");
        
        $location_level_1_score = get_score($location_level_1,"3.1.1");
        $location_level_2_score = get_score($location_level_2,"3.1.2");
        $activities_with_dac_sector_score = get_score($activities_with_dac_sector_count,"3.2");
        
        $transaction_type_commitment_score = get_score($transaction_type_commitment,"5.1");
        $transaction_type_disb_expend_score = get_score($disbursements_expenditure_count,"5.2");
        
        $document_links_score = get_score($document_links,"6.1");
        $conditions_score = get_score($conditions,"6.2");
        $result_element_score = get_score($result_element,"6.3.1");
        $result_element_with_indicator_score = get_score($result_element_with_indicator,"6.3.2");
        //$conditions_score = get_score($conditions,"6.2");
       /* $result_element_score = get_score($result_element,"Results data");
        
        
        $budget_score = get_score($budget,"Activity Budget / Planned Disbursement");
        
        
        

        $location_level_1_score = get_score($location_level_1,"Sub-national Geographic Location (text)");
        $location_level_2_score = get_score($location_level_2,"Sub-national Geographic Location (structure)");

        

        $activies_in_country_lang_score = get_score($activies_in_country_lang_count,"Activity Title or Description (Recipient language)");
*/
        store_score("2.1",$good_ids,$good_ids_score);
          $good_ids = $good_ids_score = 0;
        store_score("2.2",$activies_in_country_lang_count,$activies_in_country_lang_score);
          $activies_in_country_lang_count = $activies_in_country_lang_score = 0;
        $metadata["tests"]["2.2"]["activitiesWithOneCountry"] = $activities_with_one_recipient_country_count;
          $activities_with_one_recipient_country_count = 0;
        store_score("2.3",$start,$start_score);
         $start = $start_score = 0;
        store_score("2.4",$end,$end_score);
         $end = $end_score = 0;
        store_score("2.5",$participating_org_implementing,$participating_org_implementing_score);
          $participating_org_implementing = $participating_org_implementing_score = 0;
        store_score("2.6",$participating_org_accountable,$participating_org_accountable_score);
          $participating_org_accountable = $participating_org_accountable_score = 0;
        
        store_score("3.1.1",$location_level_1,$location_level_1_score);
          $location_level_1 = $location_level_1_score = 0;
        store_score("3.1.2",$location_level_2,$location_level_2_score);
          $location_level_2 = $location_level_2_score = 0;
        store_score("3.2",$activities_with_dac_sector_count,$activities_with_dac_sector_score);
          $activities_with_dac_sector_count = $activities_with_dac_sector_score = 0;
        
        store_score("5.1",$transaction_type_commitment,$transaction_type_commitment_score);
          $transaction_type_commitment = $transaction_type_commitment_score = 0;
        store_score("5.2",$disbursements_expenditure_count,$transaction_type_disb_expend_score);
          $disbursements_expenditure_count = $transaction_type_disb_expend_score = 0;
        //store_score("5.3",$tracable_data,$tracable_score);
        /* IF and D with IINDIVIDUAL tests
        $metadata["tests"]["5.3"]["title"] = $test_meta["test"]["5.3"]['Information Area'];
        $metadata["tests"]["5.3"]["count"] = array( "disbursements" => array("count" => $no_disbursements, 
                                                            "tracable" => $no_tracable_D_transactions,
                                                            "percentage" => $tracable_D_percent
                                                            ),
                                                          "incomingFunds" => array("count" =>  $no_incoming_funds, 
                                                                                    "tracable" => $no_tracable_IF_transactions,
                                                                                    "percentage" => $tracable_IF_percent
                                                                                    )
                                                        );
        $metadata["tests"]["5.3"]["score"] = $tracable_score;
        */
        //D and IF COMBINED
        $metadata["tests"]["5.3"]["title"] = $test_meta["test"]["5.3"]['Information Area'];
        $metadata["tests"]["5.3"]["count"] = $no_tracable_transactions;
          $no_tracable_transactions = 0;
        $metadata["tests"]["5.3"]["D+IF_Transactions"] = $transactions_that_should_be_traced;
          $transactions_that_should_be_traced = 0;
        $metadata["tests"]["5.3"]["percentage"] = $percent_tracable;
          $percent_tracable = 0;
        $metadata["tests"]["5.3"]["score"] = $tracable_score;
         $tracable_score = 0;
        store_score("6.1",$document_links,$document_links_score);
          $document_links = $document_links_score = 0;
        store_score("6.2",$conditions,$conditions_score);
          $conditions = $conditions_score = 0;
        store_score("6.3.1", $result_element,$result_element_score);
          $result_element = $result_element_score = 0;
        store_score("6.3.2",$result_element_with_indicator,$result_element_with_indicator_score);
          $result_element_with_indicator = $result_element_with_indicator_score = 0;
        //store_score("6.2",$conditions,$conditions_score);
/*
 * 
 * name: start_score
 * @param $test_id                        The test Id to store results against
 * @param $count int                      Count of reults
 * @param $scores array(score,percentage) Scores and percentage
 * @return
 * 
 */
        

/*
        //$metadata["tests"]["activityDateEnd"]["count"] = $end;
        //$metadata["tests"]["activityDateEnd"]["score"] = $end_score;

        //$metadata["tests"]["documentLink"]["count"] = $document_links;
        //$metadata["tests"]["documentLink"]["score"] = $document_links_score;

        $metadata["tests"]["result"]["count"] = $result_element;
        $metadata["tests"]["result"]["score"] = $result_element_score;

        //$metadata["tests"]["conditions"]["count"] = $conditions;
        //$metadata["tests"]["conditions"]["score"] = $conditions_score;

        $metadata["tests"]["participatingOrgImplementing"]["count"] = $participating_org_implementing;
        $metadata["tests"]["participatingOrgImplementing"]["score"] = $participating_org_implementing_score;

        $metadata["tests"]["participatingOrgAccountable"]["count"] = $participating_org_accountable;
        $metadata["tests"]["participatingOrgAccountable"]["score"] = $participating_org_accountable_score;

        $metadata["tests"]["budget"]["count"] = $budget;
        $metadata["tests"]["budget"]["score"] = $budget_score;

        $metadata["tests"]["iatiIdentifier"]["count"] = $good_ids;
        $metadata["tests"]["iatiIdentifier"]["score"] = $good_ids_score;

        $metadata["tests"]["transactionTypeCommitment"]["count"] = $transaction_type_commitment;
        $metadata["tests"]["transactionTypeCommitment"]["score"] = $transaction_type_commitment_score;

        $metadata["tests"]["transactionTypeDisbursementExpenditure"]["count"] = $disbursements_expenditure_count;
        $metadata["tests"]["transactionTypeDisbursementExpenditure"]["score"] = $transaction_type_disb_expend_score;

        $metadata["tests"]["transactionTracability"]["eligable"] = $transactions_that_should_be_traced;
        $metadata["tests"]["transactionTracability"]["count"] = $no_tracable_transactions;
        $metadata["tests"]["transactionTracability"]["score"] = $tracable_score;

        $metadata["tests"]["locationText"]["count"] = $location_level_1;
        $metadata["tests"]["locationText"]["score"] = $location_level_1_score;

        $metadata["tests"]["locationStructure"]["count"] = $location_level_2;
        $metadata["tests"]["locationStructure"]["score"] = $location_level_2_score;

        $metadata["tests"]["sector"]["count"] = $activities_with_dac_sector_count;
        $metadata["tests"]["sector"]["score"] = $activities_with_dac_sector_score;


        $metadata["tests"]["language"]["count"] = $activies_in_country_lang_count;
        $metadata["tests"]["language"]["score"] = $activies_in_country_lang_score;
*/
        $json = json_encode($metadata); 
        $json = json_format($json); //pretty it up . Function from include functions/pretty_json.php
        file_put_contents($output_file,$json);
        //die;
    }//end if excluded
} //end foreach dirs as corpus
/*
 * 
 * name: unknown
 * @param $count  int     A count of numbers tested
 * @param $test   string  Matches an id of a test to fetch data about
 * @param $total  int     Optionally supply the total against which to calculate a percentage
 * @return array          A score and a percentage
 * 
 */

function get_score($count,$test,$total = FALSE) {
  global $metadata;
  global $test_meta;
  //print_r($test_meta); die;
  
  //$test = "6.2"; 
  echo $test . PHP_EOL;
  if ($total) {
    if ($total > 0 ) {
      $percentage = 100 * round(($count/$total),4);
    } else {
      $percentage = 0;
    }
  } else {
    $percentage = 100 * round(($count/$metadata["activityCount"]),4);
  }
  //echo $percentage;
  //echo  $test_meta["test"][$test]["threashold"];
  //echo $test_meta["test"][$test]["score"];
  if ($percentage != 0 && $percentage >= $test_meta["test"][$test]["threashold"]) { 
    $score = $test_meta["test"][$test]["score"];
    echo "Test SCORE:" .  $score . PHP_EOL; //die;
  } else {
    $score = 0;
  }
  return array("score" => $score, "percentage" => $percentage);
}

function store_score($test_id,$count,$scores) {
  global $metadata;
  global $test_meta;
  $title = $test_meta["test"][$test_id]['Information Area'];
  $metadata["tests"][$test_id]["title"] = $title;
  $metadata["tests"][$test_id]["count"] = $count;
  $metadata["tests"][$test_id]["percentage"] = $scores["percentage"];
  $metadata["tests"][$test_id]["score"] = $scores["score"];
}

function count_attributes($dir) { //sorry about the silly name. Legacy code!
  $no_activity_dates = array();
  $activities_with_at_least_one = array();
  $no_activities = array();
  $found_hierarchies= array();
  $activities_with_attribute = array();
  $activity_by = array();
  
  $document_links = array();
  $result_element = array();
  $result_element_with_indicator = array();
  $conditions = array();
  
  $participating_org_accountable = array();
  $participating_org_implementing = array();
  $budget = array();
  $identifiers = array();
  
  $transaction_type_commitment = array();
  $transaction_type_disbursement = array();
  $transaction_type_expenditure = array();
  $no_disbursements = $no_incoming_funds = $no_tracable_transactions = array();
  //$no_tracable_D_transactions = $no_tracable_IF_transactions = array(); //Only used if you want to seperate out the tests on tracability per transaction type
  
  $activities_with_sector = array();
  
  $most_recent = array();
  
  $activities_with_location = array();
  $activities_with_coordinates = array();
  $activities_with_adminstrative = array();
  
  $activities_sector_assumed_dac = array();
  $activities_sector_declared_dac = array();
  
  $activies_in_country_lang = array();
  $activities_with_one_recipient_country = array();
  
  $i=0; //used to count bad id's
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
             if ($xml = simplexml_load_file($dir . $file)) {
                //print_r($xml);
                if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
                    $activities = $xml->{"iati-activity"};
                    //print_r($attributes); die;
                    foreach ($activities as $activity) {
                        //Establish the hierarchy. Save a record of all found hierarchies, and count the numbers of activities at each level
                        $hierarchy = (string)$activity->attributes()->hierarchy;
                        if ($hierarchy && $hierarchy !=NULL) {
                          $hierarchy = (string)$activity->attributes()->hierarchy;
                        } else {
                          $hierarchy = 1;
                        }
                        $found_hierarchies[] = $hierarchy; 
                        if (!isset($no_activities[$hierarchy])) {
                          $no_activities[$hierarchy] = 0;
                        }
                        $no_activities[$hierarchy]++;
                        
                        //Set up some more counters: (To count we use either simle incremental counters or collect activity ids into arrays then use 'unique' to count)
                        //These are counters, because we need to use transactions rather than activities for the count
                        if (!isset($no_disbursements[$hierarchy])) {
                          $no_disbursements[$hierarchy] = 0;
                        }
                        if (!isset($no_incoming_funds[$hierarchy])) {
                          $no_incoming_funds[$hierarchy] = 0;
                        }
                        if (!isset($no_tracable_transactions[$hierarchy])) {
                          $no_tracable_transactions[$hierarchy] = 0;
                        }
                        /*if (!isset($no_tracable_D_transactions[$hierarchy])) {
                          $no_tracable_D_transactions[$hierarchy] = 0;
                        }
                        if (!isset($no_tracable_IF_transactions[$hierarchy])) {
                          $no_tracable_IF_transactions[$hierarchy] = 0;
                        }*/
                        
                        
                        
                        
                        
                        //Elements check
                        //is <document-link>present
                        if (count($activity->{"document-link"}) > 0 || count($activity->{"activity-website"}) > 0 ) {
                          $document_links[$hierarchy][] = (string)$activity->{'iati-identifier'};
                        }
                        //<conditions> element should be present and @attached attribute should be 0 or 1
                        //if @attached=1 the <condition> element should be present
                        if (count($activity->conditions) > 0) {
                          foreach ($activity->conditions as $conds) {
                            $attached = $conds->attributes()->attached;
                            //echo $attached; die;
                            if ($attached == 0) { //pass: No conditions attached and declared as such
                              $conditions[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            } elseif ($attached == 1) { //check to see there is conditon element
                            //echo "yes";
                              if (count($conds->condition) > 0 ) { //pass: Conditons are spelled out. well almost. It will do
                                $conditions[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                            }
                          }
                        }
                        //print_r($conditions); die;
                        //<result>
                        //We count activities with <result> and with /result/indicator 
                        if (count($activity->result) > 0) {
                          $result_element[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          $result_elements = $activity->result;
                          foreach ($result_elements as $element) {
                            $result_element_with_indicator[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          }
                        }
                        
                        //More elements 
                        //Participating Organisation (Implementing)
                        $participating_orgs = $activity->{"participating-org"};
                        foreach ($participating_orgs as $participating_org) {
                          //echo (string)$activity->{"participating-org"}->attributes()->role;
                          if ((string)$participating_org->attributes()->role == "Implementing") {
                            //echo "yes";
                            $participating_org_implementing[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          }
                          //Participating Organisation (Accountable)
                          if ((string)$participating_org->attributes()->role == "Accountable") {
                            $participating_org_accountable[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          }
                        }
                        //Budget/Planned Disbursement
                        if ( count($activity->budget) > 0 || count($activity->{"planned-disbursement"}) > 0 ) {
                          $budget[$hierarchy][] = (string)$activity->{'iati-identifier'};
                        }
                        
                        //Unique Identifier check
                        //Suck up all activity identifiers - check they start with the reporting org string
                        //We count by storing the activity id in an array
                        //if there is no identifier then set a dummy one to dump it into the 'bad' pile
                        if (!isset($activity->{'iati-identifier'})) { 
                            $iati_identifier = "noIdentifierGiven" . $i;
                            $i++;
                        } else {
                          $iati_identifier = (string)$activity->{'iati-identifier'};
                        }
                        if (isset($activity->{'reporting-org'}->attributes()->ref)) {
                          $reporting_org_ref = (string)$activity->{'reporting-org'}->attributes()->ref;
                          //echo $reporting_org_ref . PHP_EOL;
                          //echo $iati_identifier . PHP_EOL;
                          if (strpos($reporting_org_ref,$iati_identifier) == 0 ) {
                            //echo "yes";
                            $identifiers[$hierarchy]["good"][] = $iati_identifier;
                          } else {
                            //echo "no";
                            $identifiers[$hierarchy]["bad"][] = $iati_identifier;
                          }
                        } else {
                          $identifiers[$hierarchy]["bad"][] = $iati_identifier;
                        }
                          
                        
                        //Financial transaction (Commitment)
                        $transactions = $activity->transaction;
                        //if (count($transactions) == 0) {
                        //  echo $id;
                          //die;
                        //}

                        if (isset($transactions) && count($transactions) > 0) { //something not quite right here
                          //Loop through each of the elements
                          foreach ($transactions as $transaction) {
                            //print_r($transaction);
                            //Counts number of elements of this type in this activity
                            //$no_transactions[$hierarchy]++;
                            //$transaction_date = (string)$transaction->{'transaction-date'}->attributes()->{'iso-date'};
                            if (isset($transaction->{'transaction-type'})) {
                              $transaction_type = (string)$transaction->{'transaction-type'}->attributes()->{'code'};
                              if ($transaction_type == "C") {
                                $transaction_type_commitment[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                              if ($transaction_type == "D") {
                                $transaction_type_disbursement[$hierarchy][] = (string)$activity->{'iati-identifier'};
                                //Count the number of disbursements at this level
                                $no_disbursements[$hierarchy]++;
                                //now test it and count the passes
                                if (isset($transaction->{"receiver-org"})) {
                                  //We have a provider-org = pass!
                                  //$no_tracable_D_transactions[$hierarchy]++;
                                  $no_tracable_transactions[$hierarchy]++;
                                }
                                //$no_disbursements = $no_incoming_funds = $no_tracable_transactions = array();
                              }
                              if ($transaction_type == "IF") {
                                //Count the number of IFs at this level
                                $no_incoming_funds[$hierarchy]++;
                                if (isset($transaction->{"provider-org"})) {
                                  //We have a provider-org = pass!
                                  //$no_tracable_IF_transactions[$hierarchy]++;
                                  $no_tracable_transactions[$hierarchy]++;
                                }
                              }
                              if ($transaction_type == "E") {
                                $transaction_type_expenditure[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                            }//if code attribute exists
                          }
                        }
                        //Going to need a count of disbursements and of IF transactions
                        //Then need to test each against a set of criteria
                            /*if ($transaction_type == NULL) {
                              $transaction_type = "Missing";
                              echo "missing";
                            }
                            if ($transaction_type !="D") {
                              echo $id;
                              //die;
                            }*/
                        
                        //Locations
                        //We can have more than one location, but they should add up to 100%
                        $locations = $activity->location;
                        //if (!isset($activities_with_location[$hierarchy])) {
                        //  $activities_with_location[$hierarchy] = 0;
                        //}
                        if (isset($locations) && count($locations) > 0) {
                          $activities_with_location[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          foreach ($locations as $location) {
                            if (isset($location->coordinates)) {
                              $activities_with_coordinates[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            }
                            if (isset($location->administrative)) {
                              if (isset($location->administrative->attributes()->adm1)) {
                                $adm1 = (string)$location->administrative->attributes()->adm1;
                              }
                              if (isset($location->administrative->attributes()->adm2)) {
                                $adm2 = (string)$location->administrative->attributes()->adm2;
                              }
                              if ( (isset($adm1) && strlen($adm1) > 0) || (isset($adm2) && strlen($adm2) > 0) ) {
                                $activities_with_adminstrative[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              }
                            }
                          }
                        }
                        
                        //Sector
                        $sectors = $activity->sector;
                        if (isset($sectors) && count($sectors) > 0) {
                          //$activities_with_sector[$hierarchy][] = (string)$activity->{'iati-identifier'};
                          foreach ($sectors as $sector) {
                            if (!isset($sector->attributes()->vocabulary)) {
                              $activities_sector_assumed_dac[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            } elseif ((string)$sector->attributes()->vocabulary == "DAC") {
                              //echo "DAC";
                              $activities_sector_declared_dac[$hierarchy][] = (string)$activity->{'iati-identifier'};
                            }
                          }
                        }
                        //Last-updated-datetime
                        $last_updated = $activity->attributes()->{'last-updated-datetime'};
                        $last_updated = strtotime($last_updated);
                        if (!isset($most_recent[$hierarchy])) {
                          $most_recent[$hierarchy] = 0;
                        }
                        if ($last_updated > $most_recent[$hierarchy]) {
                          $most_recent[$hierarchy] = $last_updated;
                        }
                        
                        //Activity dates
                        $activity_dates = $activity->{"activity-date"};
                        //if (count($activity_dates) > 0) {
                        //if ($activity_dates !=NULL) {
                        //  $activities_with_at_least_one[$hierarchy]++;
                        //}
                        foreach ($activity_dates as $activity_date) {
                          //$attributes = array("end-actual","end-planned","start-actual","start-planned");
                         // $no_activity_dates[$hierarchy]++;
                          //foreach($attributes as $attribute) {
                          $type = (string)$activity_date->attributes()->type;
                          if ($type == "start-actual" || $type =="start-planned") {
                            $type = "start";
                          }
                          if ($type == "end-actual" || $type =="end-planned") {
                            $type = "end";
                          }
                          //$date = (string)$activity_date->attributes()->{'iso-date'};
                          //Special Case for DFID
                          //$date = (string)$activity_date;
                          //echo $date; die;
                         // $unix_time = strtotime($date);
                          //if ($unix_time) {
                          //  $year = date("Y",strtotime($date));
                          //} else {
                         //   $year = 0; //we could not parse the date, so store the year as 0
                         //// }
                          //$activity_by[$year][$hierarchy][$type]++;
                          
                          $activities_with_attribute[$hierarchy][$type][]=(string)$activity->{'iati-identifier'};
                         }
                          //Languages
                          //So only testing activities where a single recipient-country is present
                          //(sum of those is the total to test i.e. less than the whole number of activities)
                          //And then testing to see if languages declared match the recipient country via a look up 
                          //And not counting them if default language of the activity matches the country default lang (why? Dunno)
                      // if($hierarchy == 2) {
                          $title_langs =  $country_langs = $description_langs = $all_langs=  array(); //Reset each of these each run through
                          
                          //Find default language of the activity
                          $default_lang = (string)$activity->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                          //echo $default_lang;
                          //Find recipient countries for this activity:
                          $recipient_countries = $activity->{"recipient-country"};
                          if (count($recipient_countries) == 1 ) { //Only test activities with one recipient country
                            //echo (string)$activity->{'iati-identifier'};
                            foreach ($recipient_countries as $country) {
                              $code = (string)$country->attributes()->code;
                              //Look up default language for this code:
                              $country_langs[] = look_up_lang($code); //We don't really need an array as there should only be one value, but it helps with the code later
                            }
                            //print_r($country_langs);
                            //Find all the different languages used on the title element
                            $titles = $activity->title;
                            foreach ($titles as $title) { //create an array of all declared languages on titles
                              $title_lang = (string)$title->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                              if ($title_lang == NULL) {
                                $title_langs[] = $default_lang;
                              } else {
                                $title_langs[] = $title_lang;
                              }
                              $title_lang = "";
                            }
                            //print_r($title_langs);die;
                            //Find all the different languages used on the description element
                            $descriptions = $activity->description;
                            foreach ($descriptions as $description) { //create an array of all declared languages on titles
                              $description_lang = (string)$description->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                              if ($description_lang == NULL) {
                                $description_langs[] = $default_lang;
                              } else {
                                $description_langs[] = $description_lang;
                              }
                              $description_lang = "";
                            }
                            //print_r($title_langs);
                            //die;
                            //Merge these arrays
                            $all_langs = array_merge($description_langs,$title_langs);
                            $all_langs = array_unique($all_langs);
                            //print_r($all_langs);
                            //Loop through the country languages and see if they are found on either the title or description
                            foreach ($country_langs as $lang) {
                              if ($lang != $default_lang && $lang != 'other') {
                                if (in_array($lang,$all_langs)) {
                                  $activies_in_country_lang[$hierarchy][] = (string)$activity->{'iati-identifier'};
                                  //echo (string)$activity->{'iati-identifier'} .PHP_EOL;

                                }
                                // FIXME this variable is badly named now
                                // Moved here to ensure that publishers are not penalised for having activities in countries with their default lang
                                $activities_with_one_recipient_country[$hierarchy][] = (string)$activity->{'iati-identifier'}; //use this to get sum of testable activities
                              }
                            }
                          }
                          //$description_lang = (string)$activity->description->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                     // }
                     
                          
                     /* if($hierarchy == 2) {
                          $title_langs = $country_langs = $description_langs = $all_langs=  array(); //Reset each of these each run through
                          //Find default language of the activity
                          $default_lang = (string)$activity->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                          echo $default_lang;
                          //Find recipient countries for this activity:
                          $recipient_countries = $activity->{"recipient-country"};
                          
                            foreach ($recipient_countries as $country) {
                              $code = (string)$country->attributes()->code;
                              //Look up default language for this code:
                              $country_langs[] = look_up_lang($code);
                            }
                              //print_r($country_langs);
                          //Find all the different languages used on the title element
                          $titles = $activity->title;
                          foreach ($titles as $title) { //create an array of all declared languages on titles
                            $title_lang = (string)$title->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                            if ($title_lang == NULL) {
                              $title_langs[] = $default_lang;
                            } else {
                              $title_langs[] = $title_lang;
                            }
                            $title_lang = "";
                          }
                          //print_r($title_langs);die;
                          //Find all the different languages used on the description element
                          $descriptions = $activity->description;
                          foreach ($descriptions as $description) { //create an array of all declared languages on titles
                            $description_lang = (string)$description->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                            if ($description_lang == NULL) {
                              $description_langs[] = $default_lang;
                            } else {
                              $description_langs[] = $description_lang;
                            }
                            $description_lang = "";
                          }
                          //print_r($title_langs);
                          //die;
                          //Merge these arrays
                          $all_langs = array_merge($description_langs,$title_langs);
                          $all_langs = array_unique($all_langs);
                          print_r($all_langs);
                          //Loop through the country languiages and see if they are found on either the title or description
                          foreach ($country_langs as $lang) {
                            if (in_array($lang,$all_langs)) {
                              $activies_in_country_lang[$hierarchy][] = (string)$activity->{'iati-identifier'};
                              echo (string)$activity->{'iati-identifier'} .PHP_EOL;
                            }
                          }
                          //$description_lang = (string)$activity->description->attributes('http://www.w3.org/XML/1998/namespace')->{'lang'};
                      } */
                          
                        
                      
                    } //end foreach
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }
  
  //if (isset($types)) {
    
    //echo "no_activities" . PHP_EOL;
    //print_r($no_activities);
    //echo "activities_with_at_least_one" . PHP_EOL;
    //print_r($activities_with_at_least_one);
    //echo "no_activity_dates" . PHP_EOL;
    //print_r($no_activity_dates);
    //echo "activity_by_year" . PHP_EOL;
    ksort($activity_by);
    //print_r($activity_by);
    //echo "activities_with_attribute" . PHP_EOL;
    //print_r($activities_with_attribute);
    //foreach($types as $attribute_name=>$attribute) {
    ///  echo $attribute_name;
//foreach($attribute as $hierarchy=>$values) {
     //   echo $hierarchy;
     //   print_r(array_count_values($values));
     // }
   // }
   
    //echo count($participating_org_implementing[0]); die;
    $found_hierarchies = array_unique($found_hierarchies);
    sort($found_hierarchies);
    //die;
    return array(//"types" => $types,
                  "no-activities" => $no_activities,
                  "activities_with_at_least_one" => $activities_with_at_least_one,
                  "no_activity_dates" => $no_activity_dates,
                  "activity_by_year" => $activity_by,
                  "hierarchies" => array_unique($found_hierarchies),
                  "activities_with_attribute" => $activities_with_attribute,
                  "document_links" => $document_links,
                  "result_element" => $result_element,
                  "result_element_with_indicator" => $result_element_with_indicator,
                  "conditions" => $conditions,
                  "participating_org_accountable" => $participating_org_accountable,
                  "participating_org_implementing" => $participating_org_implementing,
                  "budget" => $budget,
                  "identifiers" => $identifiers,
                  "transaction_type_commitment" => $transaction_type_commitment,
                  "transaction_type_disbursement" => $transaction_type_disbursement,
                  "transaction_type_expenditure" => $transaction_type_expenditure,
                  "no_disbursements" => $no_disbursements,
                  "no_incoming_funds" => $no_incoming_funds,
                  "no_tracable_transactions" => $no_tracable_transactions,
                  //"no_tracable_D_transactions" => $no_tracable_D_transactions,
                  //"no_tracable_IF_transactions" => $no_tracable_IF_transactions,
                  "activities_with_location" => $activities_with_location,
                  "activities_with_coordinates" => $activities_with_coordinates,
                  "activities_with_adminstrative" => $activities_with_adminstrative,
                  "activities_sector_assumed_dac" => $activities_sector_assumed_dac,
                  "activities_sector_declared_dac" => $activities_sector_declared_dac,
                  "most_recent" => $most_recent,
                  "activies_in_country_lang" => $activies_in_country_lang,
                  "activities_with_one_recipient_country" => $activities_with_one_recipient_country
                );
  //} else {
  //  return FALSE;
  //}
}

/*
 * Given a 2 letter country code, this will look up the default language
 * name: look_up_lang
 * @param
 * @return
 * 
 */

function look_up_lang ($code) {
  global $country_map;
  if (isset($country_map[$code])) {
    return $country_map[$code];
  } else {
    return NULL;
  }
}
?>
