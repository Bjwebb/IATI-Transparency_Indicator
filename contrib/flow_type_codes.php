<?php
//Assess sector codes in use

libxml_use_internal_errors(true);
error_reporting(0);
//Load in some settings stuff
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . '/sector_codes.csv';

//IATI  codes data for Collaboration Type
$valid_codes = array(10,20,30,35,50);

//print_r($valid_codes);die;

//DAC codes data from dac_codelist
$dac_codes = array(10,20,30,35,40,50);

//print_r($dac_codes);
//die;


//Find all data directories
$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value

foreach ($dirs as $directory) {
  echo $directory .PHP_EOL;
  $data = count_elements($dir . $directory . '/');
  $codes = $data['codes'];
  //echo count($codes) . PHP_EOL;
  $codes = array_unique($codes);
  //print_r($codes);
  //echo count($codes) . PHP_EOL;
  foreach ($codes as $code) {
    if (in_array((string)$code,$valid_codes)) {
    } else {
      $invalid_codes[] = (string)$code;
      echo (string)$code . PHP_EOL;
    }
  }
  //die;
}
//die;
$invalid_codes = array_unique($invalid_codes);
print_r($invalid_codes);
foreach ($invalid_codes as $code) {
  if (in_array($code,$dac_codes)) {
    } else {
      $non_dac_codes[] = $code;
      echo (string)$code . PHP_EOL;
    }
}
print_r($non_dac_codes);
die;


function count_elements($dir) {
  $counts = array();
  $activity_count = 0;
  $count_all_activities = 0;
  $vocabularies = array();
  $found_hierarchies = array(); 
  $no_activities = array();
  $activities_with_more_than_one_vocabulary = array();
  $failing_activities = array();
  $no_activities_with_more_than_one_of_the_same_vocab = array();
  $all_vocabs = array();
  $codes = array();
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
                    $count_all_activities += count($xml->children()); //php < 5.3
                    $activities  = $xml->xpath('//iati-activity');
                    foreach ($activities as $activity) {
                      $colab_types = array();
                      //print_r($activity); die;
                      $activity_count++;
                      //
                      $transactions = $activity->transaction;
                      foreach ($transactions as $transaction) {
                        $flow_type = $transaction->{'flow-type'};
                        if ($flow_type !=NULL){
                          if (isset($flow_type->attributes()->code)) {
                            $codes[] = $flow_type->attributes()->code;
                          }
                        }
                      }
                      
                      /*$flow_types = $activity->{'default-flow-type'};
                      
                      //print_r($colab_types);
                      if ($flow_types !=NULL){
                        if (isset($flow_types->attributes()->code)) {
                          $codes[] = $flow_types->attributes()->code;
                        }
                      }*/
//die;
                                         

                      
                    }//end foreach xml
                }//end if not organisation file
            } //end if xml is created
        }// end if file is not a system file
    } //end while
    closedir($handle);
  }

  return array("codes"=>$codes);
  //return array("activities-with" => $activities_with,
   //             "activities-without" => $activities_without);
}

function simplexml_to_array($xmlobj) {
    $a = array();
    foreach ($xmlobj->children() as $node) {
        if (is_array($node))
            $a[$node->getName()] = simplexml_to_array($node);
        else
            $a[$node->getName()] = (string) $node;
    }
    return $a;
}
?>
