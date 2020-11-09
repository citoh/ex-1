<?php

require_once("resp.php");

class JsonQuery {
    
    private $articles; //articles array
    private $authors; //authors array
    private $countries; //countries array

    private $articleNextId = 0; //articles id controller
    private $authorNextId = 0;  //articles id controller

    private $perPage = -1;  //items per page
    private $page = -1; //num of page
    private $order = "author_name"; //field to order list
    private $sort = "asc"; //asc or desc
    private $count = 0;
    private $firstPageItem = 0;
    private $lastPageItem = 0;
    

    /* ================================================================== */

    // Exercise A
    // Return a list of authors with their articles and country.
    public function endpoint_a(){
        return $this->getAllData();
    }

     /*------------------------------------------------------*/
    // Exercise B
    // Add a new article with an author that might or might now exist.
    // If the author does not exist then it should be created with an empty country.
    public function endpoint_b(){
        if(isset($_REQUEST["title"]) && isset($_REQUEST["author"])){
            $articleTitle = urldecode($_REQUEST["title"]);
            $authorName = urldecode($_REQUEST["author"]);
            $this->insertArticle($articleTitle, $authorName);
        }
        return $this->getAllData();
    }


     /*------------------------------------------------------*/
    // Exercise C
    // Update one or multiple articles title and author
    public function endpoint_c(){
        $this->updateArticles();
        $this->updateAuthors();
        return $this->getAllData();
    }

     /*------------------------------------------------------*/
    // Exercise D
    // Delete one or multiple articles.
    public function endpoint_d(){
        if( isset($_REQUEST["id"]) ||  isset($_REQUEST["ids"])){
            $ids = array($_REQUEST["ids"])[0];
            for($i = 0; $i < count($this->articles); $i++){
                for($j = 0; $j < count($ids); $j++){
                    if($this->articles[$i]['id'] == $ids[$j]){
                        unset($this->articles[$i]);
                        $i--;
                    }
                }
            }

            $this->saveJsonFile('../data/articles.json', $this->articles);
        }
        return $this->getAllData();
    }


    /* ================================================================== */


    public function __construct($articlesJsonFile, $authorsJsonFile, $countriesJsonFile) {
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

        $this->setVars();
    }


    private function loadJsonDataToArray($file){
        $data = file_get_contents($file);
        return json_decode($data, true);
    }


    private function setVars(){
        $this->count = count($this->articles);
        $this->perPage = isset( $_REQUEST["per_page"] )? $_REQUEST["per_page"] : -1;
        $this->page    = isset( $_REQUEST["page"] )? $_REQUEST["page"] : -1;
        
        if($this->page == -1 || $this->page == -1){
            $this->firstPageItem = 0;
            $this->lastPageItem  = $this->count -1;
        }else{
            $this->firstPageItem = ($this->page - 1) * $this->perPage;
            $this->lastPageItem  = $this->perPage * $this->page;
        }

        $this->order   = isset( $_REQUEST["order"] )? $_REQUEST["order"] : "author_id";
        $this->sort    = isset( $_REQUEST["sort"] )? $_REQUEST["sort"] : "asc";
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
                "country_name" => $country
            );

            $data[] = $row;
        }

        $dataResp = $this->dataToResp($data, $this->firstPageItem, $this->lastPageItem);

        $vars = array(
            "count"     => $this->count,
            "perPage"   => $this->perPage,
            "page"      => $this->page,
            "firstItem" => $this->firstPageItem,
            "lastItem"  => $this->lastPageItem,
            "order"     => $this->order,
            "sort"      => $this->sort
        );
        
        $resp = new Resp( $dataResp, $vars, "OK", 200 );
        return $resp;
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


    public function updateAuthor($id, $newName, $country_code = ""){
        $country_code = isset($this->countries[strtolower( $country_code )]) 
                        ? strtoupper($country_code)
                        : "";

        for($i = 0; $i < count($this->authors); $i++){
            if($this->authors[$i]["id"] == $id){
                $this->authors[$i]["name"] = $newName;
                $this->authors[$i]["country_code"] = $country_code;
                break; 
            }
        }
    }


    public function updateAuthors(){
        if( isset($_REQUEST["authors_ids"]) && isset($_REQUEST["authors_names"]) && 
            isset($_REQUEST["authors_countries"]) ){
            
            $authors = array();
            $authors_ids = $_REQUEST["authors_ids"];
            $authors_names = $_REQUEST["authors_names"];
            $authors_countries = $_REQUEST["authors_countries"];
            
            if( count($authors_ids) == count($authors_names) && 
                count($authors_ids) == count($authors_countries)){

                for($i = 0; $i < count($authors_ids); $i++){
                    $authors[] = array(
                        "id" => $authors_ids[$i],
                        "name" => $authors_names[$i],
                        "country_code" => $authors_countries[$i],
                    );
                }
                foreach($authors as $author){
                    $this->updateAuthor($author["id"], $author["name"], $author["country_code"]);
                }
            }

            $this->saveJsonFile('../data/authors.json', $this->authors);

        }
    }

    public function getAuthorIdByName($authorName){
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
        return $authorId;
    }

    public function insertArticle($title, $authorName){
        $authorId = $this->getAuthorIdByName($authorName);

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

    public function updateArticle($id, $title, $authorName){
        $authorId = $this->getAuthorIdByName($authorName);
        
        for($i=0; $i < count($this->articles); $i++){
            if($this->articles[$i]["id"] == $id){
                $this->articles[$i]["title"] = $title;
                $this->articles[$i]["author_id"] = $authorId;
            }
        }
    }

    public function updateArticles(){
        if( isset($_REQUEST["articles_ids"]) && isset($_REQUEST["articles_titles"]) && 
            isset($_REQUEST["authors_names"]) ){
        
            $articles = array();
            $articles_ids = $_REQUEST["articles_ids"];
            $articles_titles = $_REQUEST["articles_titles"];
            $authors_names = $_REQUEST["authors_names"];

            if( count($articles_ids) == count($articles_titles) && 
            count($articles_ids) == count($authors_names)){
                
                for($i = 0; $i < count($articles_ids); $i++){
                    $articles[] = array(
                        "id" => $articles_ids[$i],
                        "title" => $articles_titles[$i],
                        "author_name" => $authors_names[$i],
                    );
                }
                foreach($articles as $article){
                    $this->updateArticle($article["id"], $article["title"], $article["author_name"]);
                }

            }

            //$this->saveJsonFile('../data/articles.json', $this->articles);
            return $this->getAllData();

        }
    }

    
    public function deleteArticles($ids){
        for($i = 0; $i < count($this->articles); $i++){
            if( in_array($this->article[$i]["id"] ) ){
                unset( $this->article[$i]["id"] );
            }
        }
        $this->saveJsonFile('../data/articles.json', $this->articles);
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


    function sortData ($data, $order, $sort) {
        $data_a = array();
        $data_b = array();

        reset($data);

        if( !isset($data[0][$order]) ){
            return $data;
        }
       
        foreach ($data as $key => $value) {
            $data_a[] = $value[$order];
        }
        
        if($sort == "asc")
            asort($data_a);
        else
            arsort($data_a);

        foreach ($data_a as $key_a => $value_a) 
            foreach($data as $key => $value)
                if($value_a == $value[$order])
                    $data_b[$key_a] = $data[$key_a];
        return $data_b;
    }
    

    private function dataToResp($data, $firstPageItem, $lastPageItem){
        $dataResp = $this->sortData($data, $this->order, $this->sort);        
        if($this->perPage != -1 || $this->page != -1 ){
            return array_slice($dataResp, $firstPageItem, ($lastPageItem - $firstPageItem) );
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
