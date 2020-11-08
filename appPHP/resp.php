<?php
class Resp{
    public $data;
    public $vars;
    public $message;
    public $status;

    function __construct($data = array(), $vars = array(), $message = "OK", $status = 200){
        $this->data    = $data;
        $this->vars    = $vars;
        $this->message = $message;
        $this->status  = $status;
    }

    public function error404(){
        $this->message = "404 - Not found";
        $this->status  = 404;
        return $this;
    }
}