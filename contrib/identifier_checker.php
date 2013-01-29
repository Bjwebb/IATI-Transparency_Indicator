<?php
$dir = '../raw_IATI_xml_data/'; //contains dirs with each providers data in it - named by group name on registry
$dirs = scandir($dir); //all the folders in our directory holding the data
unset($dirs[0]); // unset the . value
unset($dirs[1]); //unset the .. value

$results = array();
foreach ($dirs as $corpus) {
  $path_to_corpus = $dir . $corpus . "/";
  if ($handle = opendir($path_to_corpus)) {

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
                        //echo $iati_identifier;
                        $results["count"]++;
                        if (strstr($iati_identifier,"/")) {
                          $results[$corpus]++;
                        }
                       
                    }
                    
                    
                } else { //simpleXML failed to load a file
                    //array_push($bad_files,$file);
                }
                
            }// end if file is not a system file
        } //end while
        closedir($handle);
  }
}
print_r ($results);
?>
