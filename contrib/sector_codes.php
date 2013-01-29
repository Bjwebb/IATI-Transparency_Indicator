<?php
//Assess sector codes in use

libxml_use_internal_errors(true);
//Load in some settings stuff
include ('functions/xml_child_exists.php');
include ('settings.php'); //sets $corpus, $dir and $output_dir
$output_file = $output_dir . '/sector_codes.csv';

//Get sector code data from codelist
$codelist_xml = simplexml_load_file("helpers/Sector.xml");
$codelist_codes = $codelist_xml->xpath('//codelist/Sector/code');
foreach ($codelist_codes as $code) {
  $valid_codes[] = (string)$code;
}
//print_r($valid_codes);die;

//Get sector code data from dac_codelist
$dac_codes = array();
if (($handle = fopen("helpers/dac_codelist13072012.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if (is_numeric($data[1])) {
          $dac_codes[] = $data[1];
        }
      }
}
print_r($dac_codes);
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
//$tests = array("reporting-org","transaction/transaction-date");
$fh = fopen($output_file, 'w') or die("can't open file");
/*fwrite($fh,"Element,All,,,Hierarchy 1,,,Hierarchy 2,,,\n");
fwrite($fh,",Count,Per Activity,% Activities,Count,Per Activity,% Activities,Count,Per Activity,% Activities");*/

  fwrite($fh,"Element,");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",,,,,");
  }
  fwrite($fh,"\n");
  fwrite($fh,",");
  foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Element Count,Activity Count,% Activities,>1 vocabulary,Activities with more than one of the same vocab,");
  }

$no_activities = $data["count_all_activities"];
//echo $data["number_of_activities"];
//echo $no_activities;
//print_r($data["no-activities-hierarchy"]);die;
foreach ($data["counts"] as $element=>$results) {
  //$no_xml_elements_found = 0;
  //$activities_with_all_hierarchies =0;
  //foreach ($results as $result) {
  //  $no_xml_elements_found += $result["total"];
  ////  $activities_with_all_hierarchies += $result["activities-with"];
 // }

  //$percentage = $activities_with_all_hierarchies*100/$no_activities;
  //$column1 = $element . ',' . $no_xml_elements_found . ',' . $activities_with_all_hierarchies . ',' . round($activities_with_all_hierarchies*100/$no_activities,2);
  $column1 = $element;
  fwrite($fh,"\n" . $column1);
  //at hierarchy 1
  $j=0;
  foreach ( $data["hierarchies"] as $i) {
      if (!isset($results[$i]["activities-with"])) {
        $results[$i]["activities-with"] = 0;
        $results[$i]["total"] = 0;
      } 
      if ($j==0) {
        fwrite($fh,",");
      } 
      $j++;
      //$column = ',' . $results[$i]["total"] . ',' . $results[$i]["activities-with"] . ',' . $results[$i]["without"] . ',' . round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1);
      $column = $results[$i]["total"] . ',';
      $column .= $results[$i]["activities-with"] . ',';
      $column .= round($results[$i]["activities-with"]*100/($results[$i]["activities-with"] + $results[$i]["without"]),1). ',';
      $column .= $data["activities_with_more_than_one_vocabulary"][$i] . ',';
      $column .= $data["no_activities_with_more_than_one_of_the_same_vocab"][$i] . ',';
      
      echo $column;
      fwrite($fh,$column);
  }
  //Vocabularies used
  
}
//Vocabularies used
fwrite($fh,"\n");
fwrite($fh,"\n");
fwrite($fh,"Vocabularies used\n");
foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",");
  }
fwrite($fh,"\n");
foreach ($data["all_vocabs"] as $hierarchy=>$vocabularies) {
  //Get all array values from all arrays at each hierarchy
  foreach ($vocabularies as $vocabulary) {
   $all_values[] = $vocabulary;
 }
}
 $all_values = array_unique($all_values);
 print_r($all_values);
  foreach ($all_values as $value) {
    foreach ($data["hierarchies"] as $hierarchy) {
      if (in_array($value,$data["all_vocabs"][$hierarchy])) {
        fwrite($fh,$value . ",");
      } else {
         fwrite($fh,",");
      }
      
    }
    fwrite($fh,"\n");
  }

//Failing Activities
//echo count($data["failing_activities"]);die;
fwrite($fh,"\n");
fwrite($fh,"\n");
fwrite($fh,"Failing Activities\n,");
foreach ($data["hierarchies"] as $hierarchy) {
      fwrite($fh,"Hierarchy " . $hierarchy . ",");
  }
fwrite($fh,"\ncount,");

$failing_activities = $data["failing_activities"];
$all_activities = array();
foreach ($data["hierarchies"] as $hierarchy) {
  $activities = array_unique($failing_activities[$hierarchy]);
  $count[] = count($activities);
}
$max = max($count);
//echo $max; die;

foreach ($data["hierarchies"] as $hierarchy) {
    $activities = array_unique($failing_activities[$hierarchy]);
    foreach ($activities as $activity) {
    $new_activities[$hierarchy][] = $activity;
  }
    //print_r($new_activities);die;
    fwrite($fh,count($new_activities[$hierarchy]) . ",");
}

fwrite($fh,"\n,");
//print_r($activities_at);die;
for ($i=0;$i<$max;$i++) {
  echo $i . ",";
  foreach ($data["hierarchies"] as $hierarchy) {
    echo $hierarchy .PHP_EOL;
    if (isset($new_activities[$hierarchy][$i])) {
      fwrite($fh,$new_activities[$hierarchy][$i]. ",");
    } else {
      fwrite($fh,",");
    }
  }
  fwrite($fh,"\n,");
}
fclose($fh);
//print_r($data);die;



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
                      //print_r($activity); die;
                      $activity_count++;
                      $sectors = $activity->sector;
                      //print_r($sectors); die;
                      //print_r($elements);
                      foreach ($sectors as $sector) {
                        //if ($sector->attributes()->vocabulary == "DAC") {
                        if (!isset($sector->attributes()->vocabulary) || $sector->attributes()->vocabulary == "DAC") {
                          //echo "DAC";
                          $codes[] = $sector->attributes()->code;
                        }
                      }
                                         

                      
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
