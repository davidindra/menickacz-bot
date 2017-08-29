<?php

namespace MenickaCZbot\ApiAi;

class Request
{
    private $json;

    public function __construct(string $request){
        $this->json = json_decode($request, true);
    }

    public function resolvedQuery(){
        return $this->json['result']['resolvedQuery'];
    }

    public function getParameter(string $name){
        if(isset($this->json['result']['parameters'][$name]))
            return $this->json['result']['parameters'][$name];
        else
            return false;
    }

    public function intentName(){
        return $this->json['result']['metadata']['intentName'];
    }

    public function intentId(){
        return $this->json['result']['metadata']['intentId'];
    }

    public function contexts(){
        return $this->json['result']['contexts'];
    }
}