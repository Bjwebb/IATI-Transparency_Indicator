<?php
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
//This helps us monitor how long the script takes to run.
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;
?>
<?php
//Output
echo ",Activities,,,,,,,,,Transactions,,,,,,". PHP_EOL;
//echo "Provider,No. Activities,No. w/Transactions,No. Act. with Trans. in last 30 days,1,2,3,6,12,future,other,1,2,3,6,12,future,other,Sum of array values,Transact Dates,Transacts". PHP_EOL;

echo "Provider,No. Activities,No. w/Transactions,1,2,3,6,12,future,other,1,2,3,6,12,future,other,Sum of array values,Transact Dates,Transacts,Assessment". PHP_EOL;
error_reporting(0);
$dir = '../raw_IATI_xml_data/'; //contains dirs with each providers data in it - named by group name on registry
$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value
//$dirs = array("globalgiving");

$months = array();
$now = time();
$transaction_count = 0;
$transaction_date_count = 0;
foreach ($dirs as $corpus) {
  $path_to_corpus = $dir . $corpus . "/";
  //if ($corpus == "globalgiving") {
    if ($handle = opendir($path_to_corpus) ) {
    //echo PHP_EOL . "Provider: " . $corpus . PHP_EOL;
    $transaction_count = 0;
    $transaction_date_count = 0;
    $activities = 0;
    $activities_with_transactions = 0;
        /* This is the correct way to loop over the directory. */
        while (false !== ($file = readdir($handle))) {
            if ($file != "." && $file != "..") { //ignore these system files
                //echo $file . PHP_EOL;
                //load the xml
                if ($xml = simplexml_load_file($path_to_corpus . $file)) {;
                //print_r($xml); //debug
               
                    foreach ($xml as $activity) {
                        //CHECK: Reporting Org is included in iati-identifier string
                        //$reporting_org_ref = (string)$activity->{'reporting-org'}->attributes()->ref;
                        //echo $reporting_org_ref;
                        $iati_identifier = (string)$activity->{'iati-identifier'};
                        $activities++;
                        
                        $transactions = $activity->transaction;
                        //if (count($transactions) == 0) {
                        //  echo $id;
                          //die;
                        //}

                        if (isset($transactions) && count($transactions) > 0) { //something not quite right here
                          $activities_with_transactions++;
                          //echo "yes";
                          //Loop through each of the elements
                          foreach ($transactions as $transaction) {
                            $transaction_count++; //echo $transaction_count .PHP_EOL;
                            $transaction_date = (string)$transaction->{'transaction-date'}->attributes()->{'iso-date'};
                            $time = strtotime($transaction_date);
                            if ($time) {
                              $transaction_date_count++;
                              if (($now - $time) < 0 ) {
                                $months[$corpus]["future"]++;
                                $months[$corpus]["id"]["future"][] = $iati_identifier;
                                continue;
                              } elseif (($now - $time) < 60*60*24*30 ) {
                                $months[$corpus][1]++;
                                $months[$corpus]["id"][1][] = $iati_identifier;
                                continue;
                              } elseif ($now - $time <  60*60*24*30*2 ) {
                                $months[$corpus][2]++;
                                $months[$corpus]["id"][2][] = $iati_identifier;
                                continue;
                              } elseif ($now - $time <  60*60*24*30*3 ) {
                                $months[$corpus][3]++;
                                $months[$corpus]["id"][3][] = $iati_identifier;
                                continue;
                              } elseif ($now - $time < 60*60*24*30*6 ) {
                                $months[$corpus][6]++;
                                $months[$corpus]["id"][6][] = $iati_identifier;
                                continue;
                              } elseif ($now - $time <  60*60*24*365) {
                                $months[$corpus][12]++;
                                $months[$corpus]["id"][12][] = $iati_identifier;
                                continue;
                              } else {
                                $months[$corpus]["other"]++;
                                $months[$corpus]["id"]["other"][] = $iati_identifier;
                              }
                              
                              unset($transaction_date);
                              //$month = date("m",$time);
                              //$months[$month]++;
                            }
                          }
                        }
          
                    }
                    
                    
                    
                } else { //simpleXML failed to load a file
                    //array_push($bad_files,$file);
                }
                
            }// end if file is not a system file
          
        } //end while
        closedir($handle);
      //} //if corpus -===
  }
$month_numbers = array(1,2,3,6,12,"future","other");
foreach ($month_numbers as $number) {
  if (isset($months[$corpus]["id"][$number])) {
    $activities_last_x_days = count(array_unique($months[$corpus]["id"][$number]));
    $months[$corpus]["id"][$number] = $activities_last_x_days;
    unset($activities_last_x_days);
  }
//unset($activities_last_30days); 
}
format_transaction_results($corpus,$activities,$activities_with_transactions,$transaction_date_count,$transaction_count,$months[$corpus]);
/*
print_r ($months[$corpus]);
echo PHP_EOL;
echo "Activities: " . $activities . PHP_EOL;
echo "Activities with Transactions: " . $activities_with_transactions . PHP_EOL;
echo "Transaction Dates: " . $transaction_date_count . PHP_EOL;
echo "Transactions: " . $transaction_count . PHP_EOL;
echo "Sum of array values: " . array_sum($months[$corpus]) . PHP_EOL;
//echo "Activities within last 30 days: " . count(array_unique($months[$corpus]["id"][1])) . PHP_EOL;
//make the script faster by agregating the id data
//$activities_last_30days = count(array_unique($months[$corpus]["id"][1]));
//$months[$corpus]["id"][1] = $activities_last_30days;
echo "Activities within transaction(s) (1 or more) in last 30 days: " . $months[$corpus]["id"][1] . PHP_EOL;
*/
}
//print_r($months);

function format_transaction_results ($corpus,$activities,$activities_with_transactions,$transaction_date_count,$transaction_count,$month_data) {
  $monthly_count = 0;
  $month_numbers = array(1,2,3,6,12,"future","other");
  
  echo $corpus; echo ",";
  echo $activities; echo ",";
  echo $activities_with_transactions; echo ",";
  foreach ($month_numbers as $number) {
    if (isset($month_data["id"][$number])) {
      echo $month_data["id"][$number]; 
    }
    echo ",";
  }

  
  foreach ($month_numbers as $number) {
    if (isset($month_data[$number])) {
      echo $month_data[$number]; 
    }
    echo ",";
  }
  echo array_sum($month_data); echo ",";
  echo $transaction_date_count; echo ",";
  echo $transaction_count; echo ",";
  
  //Assessment
  if ( isset($month_data[1]) ) {
    $monthly_count++;
  }
  if ( isset($month_data[2]) ) {
    $monthly_count++;
  }
  if ( isset($month_data[3]) ) {
    $monthly_count++;
  }
  if ($monthly_count > 1) {
    $assessment = "Monthly";
  }
  if ($monthly_count == 1) {
    $assessment = "Quarterly";
  }
  if (!isset($assessment)) {
    if (isset($month_data[6])) {
      $assessment = "Six-monthly";
    }
  }
  if (!isset($assessment)) {
    if (isset($month_data[12])) {
      $assessment = "Annually";
    }
  }
  if (!isset($assessment)) {
    if (isset($month_data["other"])) {
      $assessment = "Beyond one year";
    }
  }
  if (!isset($assessment)) {
    $assessment = "Can not calculate";
  }
  echo $assessment;  echo ",";
  echo PHP_EOL;
  }
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
