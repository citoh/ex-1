<?php

require_once("resp.php");

class JsonQuery {
    
    private $articles;
    private $authors;
    private $countries;

    private $articleNextId = 0;
    private $authorNextId = 0;

    private $perPage = -1;
    private $page = -1;
    private $order = "author_id";
    private $sort = "asc";
    

    /*------------------------------------------------------*/
    // Exercise A
    // Return a list of authors with their articles and country.
    public function endpoint_a(){
        return $this->getAllData();
    }

    // Exercise B
    // Add a new article with an author that might or might now exist.
    // If the author does not exist then it should be created with an empty country.
    public function endpoint_b(){
        if(isset($_REQUEST["title"]) && isset($_REQUEST["author"])){
            $articleTitle = $_REQUEST["title"];
            $authorName = $_REQUEST["author"];
            $this->insertArticle($articleTitle, $authorName);
        }
        return $this->getAllData();
    }

    // Exercise C
    // Update one or multiple articles title and author
    public function endpoint_c(){

        return $this->getAllData();
    }

    // Exercise D
    // Delete one or multiple articles.
    public function endpoint_d(){
        $ids = null;
        if( isset($_REQUEST["id"]) ){
            $ids = array($_REQUEST["id"]);
        }elseif( isset($_REQUEST["ids"]) ){
            $ids = array($_REQUEST["ids"])[0];
        }
        
        for($i = 0; $i < count($this->articles); $i++){
            if( in_array($this->articles[$i]["id"], $ids ) ){
                unset($this->articles[$i]);
            }
        }

        $this->saveJsonFile('../data/articles.json', $this->articles);

        return $this->getAllData();
    }


    /*------------------------------------------------------*/

    function __construct($articlesJsonFile, $authorsJsonFile, $countriesJsonFile) {
        $this->articles  = $this->loadJsonDataToArray( ( $articlesJsonFile ) );
        $this->authors   = $this->loadJsonDataToArray( ( $authorsJsonFile ) );
        $this->countries = $this->loadJsonDataToArray( ( $countriesJsonFile ) );

        foreach($this->articles as $article){
            $this->articleNextId = $this->articleNextId < $article["id"]? $article["id"] : $this->articleNextId;
            $this->articleNextId++; 
        }

        foreach($this->authors as $author){
            $this->authorNextId = $this->authorNextId < $author["id"]? $author["id"] : $this->authorNextId;
            $this->authorNextId++; 
        }

        $this->perPage = isset( $_REQUEST["per_page"] )? $_REQUEST["per_page"] : -1;
        $this->page    = isset( $_REQUEST["page"] )? $_REQUEST["page"] : -1;
        $this->order   = isset( $_REQUEST["order"] )? $_REQUEST["order"] : "author_id";
        $this->sort    = isset( $_REQUEST["desc"] )? $_REQUEST["desc"] : "asc";

    }


    private function loadJsonDataToArray($file){
        $data = file_get_contents($file);
        return json_decode($data, true);
    }

    private function saveJsonFile($filepath, $data){
        $content = array_values($data);
        file_put_contents($filepath, json_encode($content, JSON_PRETTY_PRINT) );
    }

    public function main(){
        return $this->getAllData();
    }


    private function getAllData(){
        $data = array();
        foreach( $this->articles as $article ){

            $author  = $this->getAuthorById( $article["author_id"] );
            $country = $this->getCountryByCode( strtolower($author["country_code"]) ); 

            $row = array(
                "article_id" => $article["id"],
                "article_title" => $article["title"],
                "author_id" => $article["author_id"],
                "author_id" => $author["id"],
                "author_name" => $author["name"],
                "country_code" => $author["country_code"],
                "country" => $country
            );

            $data[] = $row;
        }

        $dataResp = $this->dataToResp($data);

        $vars = array(
            "perPage" => $this->perPage,
            "page"    => $this->page,
            "order"   => $this->order,
            "asc"     => $this->sort
        );
        
        return $resp = new Resp( $dataResp, $vars, "OK", 200 );
    }

    public function insertAuthors($newAuthors){
    
        foreach($newAuthors as $newAuthor){
            if( isset($newAuthor["name"]) ){
                $country_code = "";
                if( isset($newAuthor["country_code"]) ){
                    $country_code = isset($this->countries[strtolower( $newAuthor["country_code"] )]) 
                                    ? strtoupper($newAuthor["country_code"])
                                    : "";
                }
                $data = array(
                    "id" => $this->authorNextId,
                    "name" => $newAuthor["name"],
                    "country_code" => $country_code
                );
                $this->authors[] = $data;
                $this->authorNextId++;

                $this->saveJsonFile('../data/authors.json', $this->authors);

                return $data["id"];
            }
            return false;
        }
    }


    public function updateAuthor($id, $newName){
        for($i = 0; $i < count($this->authors); $i++){
            if($this->authors[$i]["id"] == $id){
                $this->authors[$i]["name"] = $newName;
                break; 
            }
        }
    }


    public function insertArticle($title, $authorName){
        $authorId = null;
        foreach($this->authors as $author){
            if($author["name"] === $authorName){
                $authorId = $author["id"];
            }
        }

        if( $authorId == null ){
            $newAuthor = array( array( "name" => $authorName) );
            $authorId = $this->insertAuthors($newAuthor);
        }
        
        $newArticle = array(
            "id" => $this->articleNextId,
            "author_id" => $authorId,
            "title" => $title
        );

        $this->articles[] = $newArticle;
        $this->articleNextId++;
        
        $this->saveJsonFile('../data/articles.json', $this->articles);
        return $newArticle["id"];
    }

    
    public function deleteArticles($ids){
        for($i = 0; $i < count($this->articles); $i++){
            if( in_array($this->article[$i]["id"] ) ){
                unset( $this->article[$i]["id"] );
            }
        }
    }

    public function getAuthorById($id){
        foreach($this->authors as $author){
            if($author["id"] === $id){
                return $author;
            }
        }
        return false;
    }

    public function getCountryByCode($code){
        if(isset( $this->countries[$code] ))
            return $this->countries[$code];
        return "";
    }


    function sortData (&$data, $order, $sort) {
        $data_a = array();
        $data_b = array();
        
        reset($data);
        
        if( !isset($data[$order]) )
            return false;

        foreach ($data as $key => $value) {
            $data_a[$key] = $value[$order];
        }

        if($sort === "asc")
            asort($data_a);
        else
            arsort($data_a);
        
        foreach ($data_a as $key => $value) {
            $data_b[$key] = $data[$key];
        }
        $data = $data_b;
        
        return true;
    }
    

    private function dataToResp($data){
        $this->sortData($data, $this->order, $this->sort);
        $dataResp;
        if($this->perPage === -1 || $this->page === -1 ){
            $dataResp = $data;
        }else{

            $dataResp = array();
            $firstPageItem = $this->perPage * $this->page - $this->perPage;
            $lastPageItem  = $this->perPage * $this->page;

            for($i = $firstPageItem; $i < $lastPageItem && $i < count($data); $i++ ){
                $dataResp[] = $data[$i];
            }
        }
        return $dataResp;
    }

    public function getArticles(){
        return $this->articles;
    }


    public function getAuthors(){
        return $this->authors;
    }


    public function getCountries(){
        return $this->countries;
    }

}
