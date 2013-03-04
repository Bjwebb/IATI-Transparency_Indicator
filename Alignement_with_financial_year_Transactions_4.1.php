<?php
/*$date =  strtotime("zkgk"); 
if($date !=FALSE) {
echo date("Y_m-d",$date); 
}
die;
*/
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
//This helps us monitor how long the script takes to run.
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;
?>
<?php
//Output
//echo ",Activities,,,,,,,,,Transactions,,,,,,". PHP_EOL;
//echo "Provider,No. Activities,No. w/Transactions,No. Act. with Trans. in last 30 days,1,2,3,6,12,future,other,1,2,3,6,12,future,other,Sum of array values,Transact Dates,Transacts". PHP_EOL;

echo "Provider,No. Activities,No. w/Transactions,Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec,Sum Months,Transact Dates,Transacts,Q1,Q2,Q3,Q4,Sum,Q Count, Assessment". PHP_EOL;
error_reporting(0);
//$dir = '../raw_IATI_xml_data/'; //contains dirs with each providers data in it - named by group name on registry
include ('settings.php'); //sets $dir
$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value
//$dirs = array("dfid");


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
                            if ($time != FALSE) {
                              $transaction_date_count++;
                              $month_of_transaction = date("m",$time);
                              $months[$corpus][$month_of_transaction]++;

                              unset($month_of_transaction);
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

ksort($months[$corpus]); //important - gets the months in the correct order
//print_r($months); die;
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
  $month_numbers = array(1,2,3,6,12,"future","other");
  $quater = array();
  echo $corpus; echo ",";
  echo $activities; echo ",";
  echo $activities_with_transactions; echo ",";
  $months = array("01","02","03","04","05","06","07","08","09","10","11","12");
  foreach ($months as $key) {
    if (isset($month_data[$key])) {
      echo $month_data[$key]; 
      switch ($key) {
        case "01":
        case "02":
        case "03":
          $quater[1]++;
          break;
        case "04":
        case "05":
        case "06":
          $quater[2]++;
          break;
        case "07":
        case "08":
        case "09":
          $quater[3]++;
          break;
        case "10":
        case "11":
        case "12":
          $quater[4]++;
          break;
        default;
          break;
      }
          
    }
    echo ",";
  }
  echo array_sum($month_data); echo ",";
  echo $transaction_date_count; echo ",";
  echo $transaction_count; echo ",";
  //Quaters
  echo $quater[1]; echo ",";
  echo $quater[2]; echo ",";
  echo $quater[3]; echo ",";
  echo $quater[4]; echo ",";
  echo array_sum($quater); echo ",";
  echo count(array_keys($quater)); echo ",";
  //Assessment
  if (array_sum($quater) == 12 ) {
    $assessment = "Monthly";
  } elseif (count(array_keys($quater)) == 4) {
    $assessment = "Quaterly";
  } else {
    $assessment = "Annually";
  }
  
  echo $assessment; echo ",";
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
