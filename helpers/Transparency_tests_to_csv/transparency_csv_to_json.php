<?php
/*
 * This file is part of IATI Transparency Tests.
 *
 * Copyright 2012 caprenter <caprenter@gmail.com>
 * 
 * IATI Transparency Tests is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * IATI Transparency Tests is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with IATI Transparency Tests.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * functions/pretty_jsopn.php is taken from a comment on php.net so no claims about copyright are made.
 */


include("../functions/pretty_json.php");
$row = 0;

  if (($handle = fopen("Transparency_Indicator.csv", "r")) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ';','"')) !== FALSE) { //expecting semicolon as the delimiter
          $num = count($data) -1;
          if($row == 1) {
            $columns = $data;
            array_shift($columns);
          }
          $row++;
          //echo "<p> $num fields in line $row: <br /></p>\n";
          if (isfloat($data[0])) { //check to see if we have a test id at the start of the row
            $id = array_shift($data);
            $new_data = array();
            for ($c=0; $c < $num; $c++) {
              $new_data[$columns[$c]] = $data[$c];
            }
            //for ($c=0; $c < $num; $c++) {
              
             //   echo $data[$c] . "<br />\n";
           // }
            $tests['tests'][$id] = $new_data;
          }
      }
      fclose($handle);
  }
//print_r($tests);
$tests = json_encode($tests);
$tests = json_format($tests);
file_put_contents("test_meta_new.json",$tests);
//echo $tests;

//Thanks Boylett http://php.net/manual/en/function.is-float.php
//Returns true if the string contains a float
function isfloat($f) {
  return ($f == (string)(float)$f);
}
//print_r($codes);
?>
