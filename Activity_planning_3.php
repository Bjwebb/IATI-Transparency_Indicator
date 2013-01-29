<?php
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
//This helps us monitor how long the script takes to run.
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;
?>
<?php
error_reporting(0);
echo "Provider,Hierarchy,All Open Activities,Good Activities,%,Open but period-end check fails,Open but no budget,Difference between All Open and Good, Sum of Fails" . PHP_EOL;

$dir = '../raw_IATI_xml_data/';
$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value
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
//$exclude  = array("art19");
$exclude  = array();
//$dirs = array("dfid");
foreach ($dirs as $corpus) {
  if (!in_array($corpus,$exclude)) {
        
        $good_activities = array();
       $good_activities = test_budgets($dir . $corpus);
       
      //Set the hierarcy that we're going to test one. the rule is the lowest i.e. highest number which should be the last value of $data["hierarchies"]
      $hierarchy = end($good_activities["hierarchies"]);
      if (!isset($hierarchy)) {
         //echo $corpus; die;
       }
      //reset($good_activities["hierarchies"]); //end moves the array pointer to the end of the array, so lets rest it
       
       //print_r(array_unique($good_activities[0]));
       $complient_activities = $good_activities[0][$hierarchy];
       $open_activities = $good_activities[1][$hierarchy];
       //$open_activities_no_period_end = $good_activities[2];
       //$open_activities_no_budget = $good_activities[3];
       //print_r($complient_activities);die;
       //print_r($open_activities);
       $diff = array_diff($good_activities[1][$hierarchy],$good_activities[0][$hierarchy]);
       //print_r($diff); die;
       echo $corpus . ",";
       echo $hierarchy . ",";
       echo count($good_activities[1][$hierarchy]) . ","; //All Open activities
       echo count($good_activities[0][$hierarchy]) . ","; //All good Activities
       //Avoid division by zero
       if (count($open_activities) > 0 ) {
         //Percentage
          echo  round(((count($complient_activities)*100)/count($open_activities)),0) . ",";
        } else {
          echo "0,";
        } 
       echo count($good_activities[2][$hierarchy]) . ",";
       echo count($good_activities[3][$hierarchy]) . ",";
       echo count($diff) . ",";     
       echo count($good_activities[2][$hierarchy]) + count($good_activities[3][$hierarchy]) . PHP_EOL;  //Sum of fails - used as a check

       
       /*echo $corpus . PHP_EOL;
       echo "Good Activities: " . count($good_activities[0][$hierarchy]) . PHP_EOL;
       echo "All Open Activities: " . count($good_activities[1][$hierarchy]) . PHP_EOL;
       echo "Open but period-end check fails: " . count($good_activities[2][$hierarchy]) . PHP_EOL;
       echo "Open but no budget: " . count($good_activities[3][$hierarchy]) . PHP_EOL;
       echo "Difference between All Open and Good: " . count($diff) . PHP_EOL;
       */
       unset($hierarchy);
       //die;
  }
}
 
function test_budgets ($dir) {
  $good_activities = array();
  //$open_activities = 0;
  $open_activities = array();
  $open_activities_no_period_end = array();
  $open_activities_no_budget = array();
  $found_hierarchies = array("0");
  if ($handle = opendir($dir)) {
    //echo "Directory handle: $handle\n";
    //echo "Files:\n";

    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != "..") { //ignore these system files
            //echo $file . PHP_EOL;
            //load the xml
        if ($xml = simplexml_load_file($dir . "/" .  $file)) {
                //print_r($xml);
          //if(!xml_child_exists($xml, "//iati-organisation"))  { //exclude organisation files
            $activities = $xml->{"iati-activity"};
            //print_r($attributes); die;
            foreach ($activities as $activity) {
              //If the activity is recorded as closed, 3	Completion, 4	Post-completion, or 5	Cancelled
              //move on to the next activity
              if ((int)$activity->{'activity-status'}->attributes()->code > 3) {
                //echo $file . PHP_EOL;
                //echo (string)$activity->{'iati-identifier'};
                //die;
                continue;
              }
              //DFID ONLY check - only count hierarchy 2 activities
              /*if (strstr($dir,"/dfid")  && (int)$activity->attributes()->hierarchy != 2) {
                //echo $file . PHP_EOL;
                //echo (string)$activity->{'iati-identifier'};
                //die;
                continue;
              }*/
              //Establish the hierarchy. Save a record of all found hierarchies, and count the numbers of activities at each level
              $hierarchy = (string)$activity->attributes()->hierarchy;
              if ($hierarchy && $hierarchy !=NULL) {
                $hierarchy = (string)$activity->attributes()->hierarchy;
              } else {
                $hierarchy = "0";
              }
              $found_hierarchies[] = $hierarchy; 
              //echo $hierarchy . PHP_EOL;
              //if (!isset($no_activities[$hierarchy])) {
              //  $no_activities[$hierarchy] = 0;
              //}
              //$no_activities[$hierarchy]++;
              
              
              //ELSE work our way through
              //Get all the activity-date elements together (there will probably more than one)
              $activity_dates = $activity->{"activity-date"};
              //if (count($activity_dates) > 0) {
              //if ($activity_dates !=NULL) {
              //  $activities_with_at_least_one[$hierarchy]++;
              //}
              //Set up some markers - we're looking to see if there is an activity-date @type = end-actual
              $not_found = FALSE;
              $open_activity = FALSE;
              //Loop through them and check the types
              foreach ($activity_dates as $activity_date) {
                //$attributes = array("end-actual","end-planned","start-actual","start-planned");
                // $no_activity_dates[$hierarchy]++;
                //foreach($attributes as $attribute) {
                $type = (string)$activity_date->attributes()->type; 
                //echo $type;
                if ($type != "end-actual") {
                  $not_found = TRUE;
                  //echo "NOT FOUND";
                } else {
                  //We have an end-actual date
                  $not_found = FALSE;
                  //Use iso-date if available
                  $date = (string)$activity_date->attributes()->{'iso-date'};
                  if ($date == NULL) { //if not, then use the text in the element. If this fails the date should = 1970-01-01 I think or False so still works for us
                    $date = (string)$activity_date;
                  }
                  //echo $date;
                  //Check to see if the end-actual date is in the future
                  if (time() - strtotime($date) < 0) {
                    $open_activity = TRUE; // end-actual is in the future, so this is an open activity
                    unset($date);
                  }
                  break; //if end-actual is found we need to break out of this loop to save it being overwritten
                } // enf if type
              } //end for each activity_dates
             
              //If not found = true or date is after today, then it is OPEN
              //so we need to see if the activity contains a budget element where the period end date is beyond today
              if ($not_found == TRUE || $open_activity == TRUE) {
               
                $open_activities[$hierarchy][] = (string)$activity->{'iati-identifier'}  . $file; //echo  $open_activities . PHP_EOL;
                //echo (string)$activity->{'iati-identifier'};
                if ( count($activity->budget) > 0 ) {
                  //echo (string)$activity->{'iati-identifier'};
                  $found_good = FALSE;
                  foreach ($activity->budget as $budget) {
                    $period_end = $budget->{'period-end'};
                    //echo (string)$activity->{'iati-identifier'};
                    if (isset($period_end)) {
                       //echo (string)$activity->{'iati-identifier'};
                      if (time() - strtotime($period_end->attributes()->{'iso-date'}) < 0 ) {
                        //Budget is in the future YEAH!
                        $good_activities[$hierarchy][] = (string)$activity->{'iati-identifier'}  . $file; 
                        //echo $file . PHP_EOL;
                      //} else {
                      //echo $open_activities;
                     // echo $file . PHP_EOL;
                      //echo (string)$activity->{'iati-identifier'};
                      //die;
                      $found_good = TRUE;
                      break; //we can break out of the loop here
                      }
                    } 
                  } // end foreach budget
                  //We've looped through all the budgets. If $found_good still = False, then our opoen activity does not have a future period-end!
                  //Bad open activity!
                  if ($found_good == FALSE) {
                    $open_activities_no_period_end[$hierarchy][] = (string)$activity->{'iati-identifier'}  . $file;
                    /*echo $file;
                    echo (string)$activity->{'iati-identifier'}; 
                    echo PHP_EOL;*/
                  }
                  $found_good = FALSE;
                } else {
                  //No budgets found
                  //Bad open activitiy!
                  $open_activities_no_budget[$hierarchy][] = (string)$activity->{'iati-identifier'}  . $file;
                  /* echo $file;
                    echo (string)$activity->{'iati-identifier'}; 
                    echo PHP_EOL;*/
                }// end if count budgets
              } else {
                //echo "nope";
              } // end if not an open activity

            } //foreach activity
          //} // if xml_child_exisst
        }
      }
    }
  }        
  
  $found_hierarchies = array_unique($found_hierarchies);
  sort($found_hierarchies);
  return array($good_activities,
                $open_activities,
                $open_activities_no_period_end,
                $open_activities_no_budget,
                "hierarchies" => $found_hierarchies
                 );         
} //end test_budgets

echo PHP_EOL;
print("
Open Activities  - either:
/activity-status/@code <3 or
/activity-date@end-actual is not present or
/activity-date@end-actual is in the future.

For open activities we check to see if at least one
/budget/period-end/@iso-date is in the future");
echo PHP_EOL;
?>
<?php
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $endtime = $mtime;
   $totaltime = ($endtime - $starttime);
   echo PHP_EOL . "This page was created in ".$totaltime." seconds" . PHP_EOL;
/* JSON
 * {
   * results {
     * total => x
     * 
     * }
   * good {
     * name { 
        files {
          * a
          * b
          * c
        }
       }
     }
   * bad{}
   * */
?>
