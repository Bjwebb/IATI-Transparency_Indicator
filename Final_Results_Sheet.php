<?php
//Thanks: http://www.developerfusion.com/code/2058/determine-execution-time-in-php/
//This helps us monitor how long the script takes to run.
   $mtime = microtime();
   $mtime = explode(" ",$mtime);
   $mtime = $mtime[1] + $mtime[0];
   $starttime = $mtime;
?>
<?php
/* Take lots of CSV files and create two master final sheets
 *
 * 
 */
 
//Get a mapping of publishers and their real names
 if (($handle = fopen("helpers/publisher_mapping.csv", "r")) !== FALSE) {
    while (($pub_data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        if (isset($pub_data[5]) && $pub_data[5] !=NULL) {
          $use_this_name = $pub_data[5];
        } else {
          $use_this_name = trim($pub_data[2],",");
        }
        $publishers[$pub_data[1]] = array("name"=>$use_this_name,"group"=>$pub_data[4]);
        $groups[$pub_data[1]] = $pub_data[4];
    }
    fclose($handle);
}
 
 
 
//$new_file = array();
//Do Timeliness_Transactions_1.1.php
$dir_to_csvs = "annual_report/";
$dir_to_csvs = "";
$master_files = array("Other.csv","Signatory.csv");
$files = array( $dir_to_csvs . "Timeliness_Transactions_1.1.csv",
                $dir_to_csvs . "Timeliness_Files_1.2.csv",
                $dir_to_csvs . "Activity_planning_3.csv",
                $dir_to_csvs . "Alignement_with_financial_year_Transactions_4.1.csv",
                $dir_to_csvs . "Alignement_with_financial_year_Budgets_4.2.csv"
                );

foreach ($master_files as $master_file) {
  foreach ($files as $file) {
    switch ($file) {
      case $dir_to_csvs . "Timeliness_Transactions_1.1.csv": 
        $column = "Assessment";
        $row = 4; //human readable row number
        break;
      case $dir_to_csvs . "Timeliness_Files_1.2.csv":
        $column = "Assessment";
        $row = 5; //human readable row number
        break;
      case $dir_to_csvs . "Activity_planning_3.csv":
        $column = "%";
        $row = 6; //human readable row number
        break;
      case $dir_to_csvs . "Alignement_with_financial_year_Transactions_4.1.csv":
        $column = "Assessment";
        $row = 7; //human readable row number
        break;
      case $dir_to_csvs . "Alignement_with_financial_year_Budgets_4.2.csv":
        $column = "Assessment";
        $row = 8; //human readable row number
        break;
      default:
        break;
    }

    add_data_to_master($file,$master_file,$row,$column);
    unset($row);
    unset($column);
  }
  
}



function add_data_to_master($file,$master_file,$row,$column) {
  //global $new_file;
  $new_file = array();
  global $publishers;
  global $dir_to_csvs;
  
  //Open the Master file
  if (($handle2 = fopen("csv/" . $master_file, "r")) !== FALSE) {
    //Grab and store the first line
    $row1 = fgetcsv($handle2, 0, ',','"'); // read and store the first line
    //print_r($row1);die;
    $new_file[] = $row1; //shove it in the array we will use to print results
    
    //Get to the row we want to add values in
    $i = 1;
    while (($data2 = fgetcsv($handle2, 2000, ",")) !== FALSE) {
      $i++;
      if ($i == $row) {
        //We're at our row
        //For each provider column we need to add the correct data..
        //from the file containing the results
        if (($handle = fopen("csv/" . $file, "r")) !== FALSE) {
          //echo $file; die;
          $results_row1 = fgetcsv($handle, 0, ',','"'); // read and store the first line
          //Timeliness_Transactions_1.1.csv - has column headings on row2
          if ($file == $dir_to_csvs . "Timeliness_Transactions_1.1.csv" ) {
            $results_row1 = fgetcsv($handle, 0, ',','"'); // read and store the first line
          }
          //print_r($results_row1);die;
          while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
            $provider = $data[0]; //get the provider id
            //echo $provider; die;
            if (isset($publishers[$provider]["name"])) {
              $provider = $publishers[$provider]["name"];
            }
            if ($provider == NULL || strstr($provider,"This page")) {
              continue;
            }
            //echo $provider; die;
            //Is this provider in the master file?
            foreach ($row1 as $key=>$column_name) { //$row1 contains provider names as column headers
              if ($column_name == $provider) { //Yes we have the provider, now add data to this column
                $column_number = $key; //so now we know which column to store the data in
                //buut what data?
                //grab it from
                //echo $column_number; die;
                foreach ($results_row1 as $key2=>$value) { //e.g. $row1[0] = projectid
                  if (trim($value) == $column) { //we've found the column we need
                    //Store it in the file
                    /*if (!isset ($data[$key2])) {
                      echo $key2 . PHP_EOL;
                      echo $value . PHP_EOL;
                      echo $column . PHP_EOL;
                      echo $file;
                      die;
                    }*/
                    $store = $data[$key2];
                    //echo $store; die;
                    //echo $column_number; die;
                    $data2[$column_number] = $store; 
                  }
                } 
              }
            }
            //echo $provider; die;
            //Get the data that needs to go in the master sheet
          } //end while $data
        }   //end if handle     //$data2[$column_number] = $store; 
      } //end if $ ==$row
      $new_file[] = $data2;
    } //end while $data2
    fclose($handle);
    fclose($handle2);
  }
   $fp = fopen("csv/" . $master_file, 'w');
      foreach ($new_file as $fields) {
      fputcsv($fp, $fields,",");
    }
    fclose($fp);
    
    //die;
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
