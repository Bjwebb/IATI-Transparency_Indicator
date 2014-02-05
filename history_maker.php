<?php
/*
 * Use the CKAN data to get the file history on each file on the regisrty
 * Save the data for each publisher
 * 
 * To Do:
 * Add logs
 * Only update recently updated files
 */
$CKAN_data_directory = "helpers/CKAN_meta/CKAN_data";
 
$year = date("Y");
$files = scandir($CKAN_data_directory . "/" . $year . "/");
sort($files); //We need to do this to make sure the newest directory is last in the array so the following array pop will work.
//print_r($files);
$most_recent_day_dir = array_pop($files);
echo $most_recent_day_dir;

$most_recent_day_file = $CKAN_data_directory . "/" . $year . "/" . $most_recent_day_dir . "/all_files_metadata.php";

$most_recent_day_data = file_get_contents($most_recent_day_file);
$most_recent_day_data = unserialize($most_recent_day_data);
print_r($most_recent_day_data); 

$i=0;
foreach ($most_recent_day_data as $ckan_file) {
  /*if ($i>1) {
    break;
  }
  $i++;
  */
  $package_name = $ckan_file["name"];
  $group = strtok($package_name, '-');
  $filename = $package_name;// $ckan_file["file"];
  if (!is_dir("./history/" . $group)) {
    mkdir("./history/" . $group);
  }
  fetch_ckan_json($package_name,"./history/" . $group . "/" . $filename);
  
}



/*
curl -X POST -d '{"id":"dfid-189"}'
http://iatiregistry.org/api/3/action/package_revision_list


*/

function fetch_ckan_json ($id,$output_file) {
  $url = "http://iatiregistry.org/api/3/action/package_revision_list";
  echo PHP_EOL . $id;
  //Fetch the data using curl and save to the output file
  $ch = curl_init();
  $fp = fopen($output_file, "w"); 
  
  // set URL and other appropriate options
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS, '{"id":"' . $id . '"}') ;
  curl_setopt($ch,CURLOPT_TIMEOUT,180);
  curl_setopt($ch,CURLOPT_FAILONERROR, true);
  //Follow re-directs:
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch, CURLOPT_FILE, $fp);
  //curl_exec($ch);
  if(curl_exec($ch) === false) {
    echo ' - Curl error: ' . curl_error($ch) . PHP_EOL;
    $error = TRUE;
  } else {
    echo ' - Operation completed without any errors' . PHP_EOL;
    $error = FALSE;
  }
  curl_close($ch); // close cURL handler
  fclose($fp);
  if (isset($error) && $error == TRUE) {
     echo ' - Sorry, we can\'t grab the most up to date data.' . PHP_EOL;
     return FALSE;
  } else {
     return TRUE;
  } //end if output empty else..
}
?>

  
?>

