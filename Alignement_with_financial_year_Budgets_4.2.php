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
echo "Provider,Activities w/ budgets,Passing Activities,%,Budgets Counted,Passing Budgets,%,Mean Spread,Median Spread, Mode Spread, Spread Range,Assessment" . PHP_EOL;

//$dir = '../raw_IATI_xml_data/';
include ('settings.php'); //sets $dir
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
$exclude  = array("art19");
$exclude  = array();
//$dirs = array("dfid");
foreach ($dirs as $corpus) {
  if (!in_array($corpus,$exclude)) {
        
      $results = array();
      $results = test_budget_spread($dir . $corpus);
       
      $activities_with_budget = count($results[0]);
      $good_activities = $results[1];
      $passing_budgets = count($good_activities);
      $passing_activities = count(array_unique($good_activities));
      $budgets_counted = $results[2];
      $all_spreads = $results[3];
      
       echo $corpus . ",";
       echo $activities_with_budget . ",";
       echo $passing_activities . ",";
       if ($activities_with_budget > 0 ) {
        echo intval($passing_activities*100/$activities_with_budget) . ",";
       } else {
         echo "0" . ",";
       }
       echo $budgets_counted . ",";
       echo $passing_budgets . ",";
       if ($budgets_counted > 0 ) {
         echo round(($passing_budgets*100/$budgets_counted),0) . ",";
       } else {
         echo "0" . ",";
       }
       echo round(mmmr($all_spreads, 'mean'),2) . ",";
       $median = mmmr($all_spreads, 'median'); //returns false if not available
       echo $median . ",";
       echo mmmr($all_spreads, 'mode') . ",";
       echo mmmr($all_spreads, 'range') . ",";
       
       if ($median) {
         switch ($median) {
           case ($median <100 && $median > 0):
            $assesment = "quaterly";
            break;
          case ($median <370 && $median > 100):
            $assesment = "annual";
            break;
          default:
            $assesment = ">370";
            break;
          }
          echo $assesment . ",";
        } else {
          echo "not known" . ",";
        }
          
       
       echo PHP_EOL;       
       
       //unset($all_spreads);
       /*echo $corpus . PHP_EOL;
       echo "Good Activities: " . count($good_activities[0][$hierarchy]) . PHP_EOL;
       echo "All Open Activities: " . count($good_activities[1][$hierarchy]) . PHP_EOL;
       echo "Open but period-end check fails: " . count($good_activities[2][$hierarchy]) . PHP_EOL;
       echo "Open but no budget: " . count($good_activities[3][$hierarchy]) . PHP_EOL;
       echo "Difference between All Open and Good: " . count($diff) . PHP_EOL;
       */
       //unset($hierarchy);
       //die;
  }
}
 
function test_budget_spread ($dir) {
  
  $activities_with_budget = array();
  $good_activities = array();
  $budget_count = 0;
  $all_spreads = array();
  /*$good_activities = array();
  //$open_activities = 0;
  $open_activities = array();
  $open_activities_no_period_end = array();
  $open_activities_no_budget = array();
  $found_hierarchies = array("0");
  */
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
              
              /*
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
              */
              
              
                if ( count($activity->budget) > 0 ) {
                  //echo (string)$activity->{'iati-identifier'};
                  $activities_with_budget[] = (string)$activity->{'iati-identifier'}  . $file; 
                  foreach ($activity->budget as $budget) {
                    $budget_count++;
                    $period_start = $budget->{'period-start'};
                    $period_end = $budget->{'period-end'};
                    //echo (string)$activity->{'iati-identifier'};
                    if (!empty($period_end) && !empty($period_start)) {
                       //echo (string)$activity->{'iati-identifier'};
                      if (isset($period_end->attributes()->{'iso-date'})) {
                         $period_end = strtotime($period_end->attributes()->{'iso-date'});
                      } else {
                          $period_end = strtotime((string)$period_end);
                      }
                      if (isset($period_start->attributes()->{'iso-date'})) {
                         $period_start = strtotime($period_start->attributes()->{'iso-date'});
                      } else {
                          $period_start = strtotime((string)$period_start);
                      }
                      
                      if ($period_end != FALSE && $period_start != FALSE) {
                        $spread = ($period_end - $period_start)/(60*60*24);
                        $all_spreads[] = $spread;
                        /*echo $period_end . PHP_EOL;
                        echo $period_start . PHP_EOL;
                        echo $spread . PHP_EOL;
                        echo (string)$activity->{'iati-identifier'}  . $file . PHP_EOL;
                        */
                        unset($period_end);
                        unset($period_start);
                        if ($spread > 0 && $spread < 93) {
                           //NB a count of $good_activities should tell us how many budget elements pass the test
                           //$budget_count tells us how many budget elements are tested
                           //array_unique on $good_activities tells us how many activities pass the test
                           //array_count_values could be used to tell us how many budgets in each activity pass the test
                           $good_activities[] = (string)$activity->{'iati-identifier'}  . $file; 
                           
                           //break; //we can break out of the loop here because we only need to find a single budget that conforms - this might need checking
                        }
                        unset($spread);
                      }
                    } //if empty
                  } // end foreach budget
                } // if there are budgets
             

            } //foreach activity
          //} // if xml_child_exisst
        }
      }
    }
  }        
  

  return array( $activities_with_budget,
                $good_activities,
                $budget_count,
                $all_spreads
                 );         
} //end test_budget_spread

/*Mean, Media, Mode function taken from http://phpsnips.com/45/Mean,-Median,-Mode,-Range-Of-An-Array
 * Thanks!
 * 
 * $arr = array(12,33,23,4,20,124,4,2); 

// Mean = The average of all the numbers 
echo 'Mean: '.mmmr($arr).'<br>'; 
echo 'Mean: '.mmmr($arr, 'mean').'<br>'; 

// Median = The middle value after the numbers are sorted smallest to largest 
echo 'Median: '.mmmr($arr, 'median').'<br>'; 

// Mode = The number that is in the array the most times 
echo 'Mode: '.mmmr($arr, 'mode').'<br>'; 

// Range = The difference between the highest number and the lowest number 
echo 'Range: '.mmmr($arr, 'range'); 
 * 
 * 
 */
function mmmr($array, $output = 'mean'){ 
    if(!is_array($array)){ 
        return FALSE; 
    }else{ 
        switch($output){ 
            case 'mean': 
                $count = count($array); 
                $sum = array_sum($array); 
                $total = $sum / $count; 
            break; 
            case 'median': 
                rsort($array); 
                $middle = round(count($array) / 2); 
                $total = $array[$middle-1]; 
            break; 
            case 'mode': 
                $v = array_count_values($array); 
                arsort($v); 
                foreach($v as $k => $v){$total = $k; break;} 
            break; 
            case 'range': 
                sort($array); 
                $sml = $array[0]; 
                rsort($array); 
                $lrg = $array[0]; 
                $total = $lrg - $sml; 
            break; 
        } 
        return $total; 
    } 
} 
echo PHP_EOL;
print(
"Notes:
'passing' budgets have <93 days between period-start and period-end.
Assessment is based on Median Spread:
<100 days = quarterly
<370 = annual");
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
