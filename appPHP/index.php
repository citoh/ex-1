<?php 

/*
The exercise can be found in https://github.com/rubenfil/ex-1.

Submit your changes with a pull request from a different branch.
For the sake of this exercise, data should be retrieved and/or updated 
from the JSON files inside the "data" folder.
Please do the tasks below.

1. Create an endpoint for each of these:
  a. Return a list of authors with their articles and country.
  b. Add a new article with an author that might or might now exist.
      If the author does not exist then it should be created with an empty country.
  c. Update one or multiple articles title and author.
  d. Delete one or multiple articles.

2. Create a middleware for endpoint "a", that will look for pagination parameters 
in the request URL and create an object with it. Endpoint with parameters example:
http://localhost:8080/endpoint_a?per_page=5&page=2&order=name&sort=desc

Then use this object inside endpoint "a" to filter which authors will be returned.
*/

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

header('Content-Type: application/json');

require_once("json-query.php");
require_once("routes-helper.php");
require_once("resp.php");

//Data
$articlesJsonFile   = "../data/articles.json"; 
$authorsJsonFile    = "../data/authors.json";
$countriesJsonFile  = "../data/countries.json";

//Json Files and Returns Controller
$jsonQuery = new JsonQuery( $articlesJsonFile, $authorsJsonFile, $countriesJsonFile );

//Requests Controls
$root = "http://".$_SERVER['HTTP_HOST'];
$root .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
$routesHelper = new RoutesHelper($root);

$actionRequest = $routesHelper->getActionRequest();
$actionRequest = empty($actionRequest)?"main":$actionRequest;
$resp = new Resp();

if(is_callable( array( $jsonQuery, $actionRequest ) ) ){
    $resp =  $jsonQuery->$actionRequest();
    echo json_encode( $resp );
}else{
    echo json_encode( $resp->error404() );
}

if(isset($resp->status)){
    http_response_code($resp->status);
}