<?php

/*
go through a history directory and work out timelyness
[timestamp] => 2012-03-17T00:35:09.496097
[message] => REST API: Update object aa-activity
[author] => iati-archiver


*/
 echo "Provider,Number of files who's most recent update was in the last [days],,,,,,Number of updates in last 180 days- breakdown,,,,,, " . PHP_EOL;
//ksort($updated);
//print_r($updated); 
echo ",";
$intervals = array(30,60,90,180,370,"other"); 
foreach ($intervals as $interval) {
  echo $interval;  echo ",";
} 
foreach ($intervals as $interval) {
  echo $interval;  echo ",";
} 
echo PHP_EOL;


$dir = "history";
$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value

$groups = $dirs;
//print_r($groups); die;
foreach ($groups as $group) {
  if (is_dir($dir . "/" . $group)) {
    if ($handle = opendir($dir . "/" . $group)) {
      //echo "Directory handle: $handle\n";
      //echo "Files:\n";
      $days = array();
      /* This is the correct way to loop over the directory. */
      while (false !== ($file = readdir($handle))) {
        if ($file != "." && $file != "..") { //ignore these system files
          //echo $file . PHP_EOL;
          $data = file_get_contents($dir . "/" . $group . "/" .$file);
          $data = json_decode($data,TRUE);
          
          foreach ($data["result"] as $record) {
            $timestamp = $record["timestamp"];
            $message = $record["message"];
            $author = $record["author"];
            if ($author == "iati-archiver" && strstr($message,"update dataset")) {
              $last_updated = time() - strtotime($timestamp);
              $days[$file][] = round($last_updated/(60*60*24),0);
            }
          }
          
          //echo $group;
          //print_r($data);
          //die;
        } //if file
      } //while files in  dir
      //echo "Number of files: " . count($days) . PHP_EOL;
      $monthly_count = 0;
      $number_of_updates = 0;
      $number_of_updates_array = array();
      foreach ($days as $file) {
        foreach ($file as $updates) {
          if ($updates < 30) {
           $number_of_updates_array[30]++;
          } 
          if ($updates < 60) {
            $number_of_updates_array[60]++;
          } 
          if ($updates < 90) {
            $number_of_updates_array[90]++;
          } 
          //if ($updates < 120) {
          //  $number_of_updates_array[120]++;
          //} 
          if ($updates < 180) {
            $number_of_updates_array[180]++;
          } 
          if ($updates < 370) {
            $number_of_updates_array[370]++;
          }
          if ($updates > 370) {
            $number_of_updates_array["other"]++;
          }
          
          
          //if ($updates < 180) {
           // $number_of_updates++;
          //}
        }
        
        if ($file[0] < 30) {
          $updated[30]++;
          continue;
        } elseif ($file[0] < 60) {
          $updated[60]++;
          continue;
        } elseif ($file[0] < 90) {
          $updated[90]++;
          continue;
        //} elseif ($file[0] < 120) {
        //  $updated[120]++;
        //  continue;
        } elseif ($file[0] < 180) {
          $updated[180]++;
          continue;
        } elseif ($file[0] < 370) {
          $updated[370]++;
          continue;
        } elseif ($file[0] > 370) {
          $updated["other"]++;
          continue;
        }
      }
      
      
      
      echo $group; echo ",";
      foreach ($intervals as $interval) {
        if (isset($updated[$interval])) {
          echo $updated[$interval];  echo ",";
        } else {
           echo ",";
        }
      } 
      
      //echo "Number of updates in last 180 days: " . $number_of_updates . PHP_EOL;
      //echo PHP_EOL;
      //echo "Number of updates in last 180 days- breakdown: " . PHP_EOL;      
      //ksort($number_of_updates_array);
     // print_r($number_of_updates_array); 
      foreach ($intervals as $interval) {
        if (isset($number_of_updates_array[$interval])) {
          echo $number_of_updates_array[$interval];  echo ",";
        } else {
           echo ",";
        }
      } 
      
      
      //Assessment
      if ( isset($number_of_updates_array[30]) ) {
        $monthly_count++;
      }
      if ( isset($number_of_updates_array[60]) ) {
        $monthly_count++;
      }
      if ( isset($number_of_updates_array[90]) ) {
        $monthly_count++;
      }
      if ($monthly_count > 1) {
        $assessment = "Monthly";
      }
      if ($monthly_count == 1) {
        $assessment = "Quarterly";
      }
      if (!isset($assessment)) {
        if (isset($number_of_updates_array[180])) {
          $assessment = "Six-monthly";
        }
      }
      if (!isset($assessment)) {
        if (isset($number_of_updates_array[370])) {
          $assessment = "Annually";
        }
      }
      if (!isset($assessment)) {
        if (isset($number_of_updates_array["other"])) {
          $assessment = "Beyond one year";
        }
      }
      if (!isset($assessment)) {
        $assessment = "Can not calculate";
      }
      echo $assessment;  echo ",";

      
      
      
      
      echo PHP_EOL;
      //echo "Raw Data" . PHP_EOL;
     //print_r($days); 
     
     
     
     
     
     
      unset($assessment);
      unset($number_of_updates_array);
      unset($updated);
      
      
      
      //die;
    } //if $handle
  } //if is_dir($group)
} //foreach group
