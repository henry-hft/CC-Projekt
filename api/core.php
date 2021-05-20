<?php
// enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
  
// home url
$home_url="https://cc-ss21.herokuapp.com/api/";
  
// page given in URL parameter, default page is one
$page = isset($_GET['page']) ? $_GET['page'] : 1;
  
// set number of records per page
$records_per_page = 10;
  
// calculate for the query LIMIT clause
$from_record_num = ($records_per_page * $page) - $records_per_page;
?>
