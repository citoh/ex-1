<?php

class RoutesHelper {
    private $urlRequest;
    private $baseUrl;
    private $actionRequest;

    function __construct($baseUrl, $actionsRoutes = null){
        $this->urlRequest = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $this->baseUrl = $baseUrl;

        $this->actionRequest = str_replace($baseUrl,'',$this->urlRequest);
        $this->actionRequest = explode("?", $this->actionRequest)[0];
    }

    public function getBaseUrl(){
        return $this->baseUrl;
    }

    public function urlRequest(){
        return $this->urlRequest;
    }

    public function getActionRequest(){
        return $this->actionRequest;
    }
 
}