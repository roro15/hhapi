<?php

class EntityData {
    
    protected $data;
    
    public function __construct($data) {
        $this->json = $data;
    }
    
    public function __get($name) {
        return property_exists($this->data, $name) ? $this->data->$name : null;
    }
}