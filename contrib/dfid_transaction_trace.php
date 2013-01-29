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
error_reporting(0);

//Output
echo "Provider,Name,No. Activities,No. Acts. w/Transactions, No. Trans., No. IF Trans.,No. IF Trans. w/ Ref,No. Acts. w/Ref,No. IF Trans w/prov-act-id,No. Acts w/prov-act-id". PHP_EOL;

$dir = '../raw_IATI_xml_data/'; //Assumes the following data structure ../raw_IATI_xml_data/<provider>/*.xml
                                //e.g. ../raw_IATI_xml_data/cafod/IATIFile_Afghanistan.xml
$dirs = array("gb-cc-220949" => "British Red Cross",
              "cafod" => "Catholic Agency For Overseas Development",
              "dipr" => "Development Initiatives Poverty Research",
              "aa" => "International HIV\/AIDS Alliance",
              "mapaction" => "MapAction",
              "oxfamgb" => "Oxfam GB",
              "plan_uk" => "Plan International UK",
              "progressio" => "Progressio",
              "scuk" => "Save the Children UK",
              "self-help-africa" => "Self Help Africa",
              "transparency-international" => "Transparency International Secretariat",
              "wateraid" => "WaterAid",
              "womankindworld" => "Womankind Worldwide",
              "wvuk" => "World Vision UK",
              "ai_1064413" => "African Initiatives",
              "akfuk73" => "Aga Khan Foundation (United Kingdom)",
              "camfed" => "Camfed International",
              "canoncollinstrust" => "Canon Collins Trust",
              "cic" => "Children in Crisis",
              "hp_12" => "HealthProm",
              "karuna" => "Karuna Trust",
              "mcs" => "Mercy Corps Scotland",
              "mrdf" => "Methodist Relief and Development Fund",
              "opportunity-international-uk" => "Opportunity International UK",
              "spuk" => "Samaritan's Purse UK",
              "sendacow" => "Send a Cow",
              "sense_international" => "Sense International",
              "sossaheluk" => "SOS Sahel International UK",
              "surf" => "Survivors Fund (SURF)",
              "tearfund" => "Tearfund",
              "traidcraft" => "Traidcraft Exchange",
              "tao-03473165" => "Trust for Africa's Orphans",
              "wsup" => "Water & Sanitation for the Urban Poor (WSUP)",
              "icauk" => "Institute of Cultural Affairs (ICA)",
              "ri-uk" => "Relief International – UK"
              );


foreach ($dirs as $corpus => $name) {
  $path_to_corpus = $dir . $corpus . "/";
  //if ($corpus == "globalgiving") {
    if ($handle = opendir($path_to_corpus) ) {
    //echo PHP_EOL . "Provider: " . $corpus . PHP_EOL;
    $transaction_count = 0;
    //$transaction_date_count = 0;
    $activities = 0;
    $activities_with_transactions = 0;
    $activities_with_IF_transactions = 0;
    $reporting_dfid = array();
    $reporting_dfid_activity_id = array();
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
                            $transaction_type = (string)$transaction->{'transaction-type'}->attributes()->{'code'};
                            if (strtolower($transaction_type) == "if") {
                              $activities_with_IF_transactions++;
                              
                              //Now check to see if:
                              //they have dfid GB-1 in the refs and the provider-activity id as well
                              //<provider-org ref="GB-1" provider-activity-id="GB-1-202035-101" />
                              //If so then store the iati identifier (and append the file name) - we can count them up later
                              $provider_org_ref = (string)$transaction->{'provider-org'}->attributes()->{'ref'};
                              $provider_activity_id = (string)$transaction->{'provider-org'}->attributes()->{'provider-activity-id'};
                              if ($provider_org_ref == "GB-1") {
                                $reporting_dfid[] = (string)$activity->{'iati-identifier'} . $file;
                              }
                              if (strstr($provider_activity_id,"GB-1")) {
                                $reporting_dfid_activity_id[] = (string)$activity->{'iati-identifier'} . $file;
                              }
                            } //if trans. type = I
                          }//forech transaction
                        } //if transactions exist
          
                    } //foreach activity
                    
                    
                    
                } else { //simpleXML failed to load a file
                    //array_push($bad_files,$file);
                }
                
            }// end if file is not a system file
          
        } //end while
        closedir($handle);
      //} //if corpus -===
  }


format_transaction_results($dirs,$corpus,$activities,$activities_with_transactions,$transaction_count,$activities_with_IF_transactions,$reporting_dfid,$reporting_dfid_activity_id);

}
//print_r($months);

function format_transaction_results ($dirs,$corpus,$activities,$activities_with_transactions,$transaction_count,$activities_with_IF_transactions,$reporting_dfid,$reporting_dfid_activity_id) {
  $month_numbers = array(1,2,3,6,12,"future","other");
  
  echo $corpus; echo ",";
  echo $dirs[$corpus]; echo ",";
  echo $activities; echo ",";
  echo $activities_with_transactions; echo ",";
  echo $transaction_count; echo ",";
  echo $activities_with_IF_transactions; echo ",";
  echo count($reporting_dfid); echo ",";
  echo count(array_unique($reporting_dfid)); echo ",";
  echo count($reporting_dfid_activity_id); echo ",";
  echo count(array_unique($reporting_dfid_activity_id)); echo ",";
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
