<?php
/* This script uses the CKAN API to return a minimal amount of data on all the packages it holds
 * We use this to create a record of which files were on the registry on a certain date
 * (stored in ../CKAN_data)
 * and we use the metadata to determine if we need to update the raw xml for each file
 */
 
//The data this script generates is mostly temporary data
// ckan_x.json, last_run.txt
$tmp_file_store = "tmp/";

//Check to see when the update was last run
//We store the info in a txt file, so if that exists, we have run this at least once before
if (file_exists("last_run.txt")) {
  $last_run = file_get_contents($tmp_file_store . "last_run.txt");
} else {
  //No file, so set the date to yesterday
  $last_run = date("Y-m-d H:i:s",strtotime("-1 day"));
}
//echo $last_run;
//echo strtotime($last_run) .PHP_EOL;
//echo time() - strtotime("-1 day") .PHP_EOL;
//If last run is more than a day old then go get some data from CKAN
if (strtotime($last_run) <= (time() - (60*60))) {
  echo "fetching";
  fetch_ckan_json(0,$tmp_file_store . "ckan_0.json"); //Fetches up to 1000 records
}
//die;
//Now interrogate the returned data to find the count of actual results available 
//and calculate how many more pages of results we need to fetch
//(or exisitng data if we have no need to fetch to help find out how many files we have)
$data = file_get_contents($tmp_file_store . "ckan_0.json");
$data = json_decode($data);
$count = $data->count; //Number of available results
echo $count  . PHP_EOL;

//$i is going to tell us the number of additional pages to fetch
$i=-1;
if ($count > 1000) {
  while ($count > 0 ) {
    $i++;
    $count = $count - 1000;
  }
}
//echo $i;
$number_files = $i;
//If $i is greater than zero then we have more pages to fetch
//Fetch them and save them.
if (strtotime($last_run) <= (time() - (60*60))) {
  while ($i>0) {
    $offset = $i * 1000;
    echo "fetching" . $i . PHP_EOL;
    fetch_ckan_json($offset,$tmp_file_store . "ckan_" .$i . ".json");
    $i--;
  }
}
//Store the last run time
file_put_contents($tmp_file_store . "last_run.txt",date("Y-m-d H:i:s",time()));

//Merge all the results into one big file
$all_data = array();
$i = $number_files;
while ($i>=0) {
  $data = file_get_contents($tmp_file_store . "ckan_" .$i . ".json");
  $data = json_decode($data,true);
  $all_data = array_merge($all_data,$data["results"]);  

  $i--;
}

echo count($all_data) . PHP_EOL;
foreach ($all_data as $result) {
  $file_metadata[$result["id"]] = array( "group"=> $result["groups"][0],
                                    "file" => basename($result["download_url"]),
                                    "url" => $result["download_url"],
                                    "name" => $result["name"],
                                    "metadata_modified" => $result["metadata_modified"],
                                    //"data_updated" => $data_updated,
                                    "this_record_saved_at" => time()
                                    );
}

//Serialize and save our metadata array
//this places it in a folder mapped to year and day.
$CKAN_data_directory = "CKAN_data";
$year = date("Y");
$day = date("z");
if (!is_dir($CKAN_data_directory . "/" . $year . "/" . $day)) {
  mkdir($CKAN_data_directory . "/" . $year);
  mkdir($CKAN_data_directory . "/" . $year . "/" . $day);
}
$output_file = $CKAN_data_directory . "/" . $year . "/" . $day . "/all_files_metadata.php";
file_put_contents($output_file,serialize($file_metadata));
$file_metadata = file_get_contents($output_file);
$test = unserialize($file_metadata);
print_r($test);





/*Fetch some ckan data*/

function fetch_ckan_json ($offset,$file,$limit=1000) {
  $url = "http://iatiregistry.org/api/search/dataset?fl=name,download_url,metadata_modified,groups,id,data_updated";
  
  //Fetch the data using curl and save to the output file
  $ch = curl_init();
  $fp = fopen($file, "w"); 
  
  // set URL and other appropriate options
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch,CURLOPT_POSTFIELDS, 'offset=' . $offset . '&limit=' . $limit);
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
