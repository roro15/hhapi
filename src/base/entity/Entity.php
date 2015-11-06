<?php

namespace entity;

abstract class Entity {
    
    protected $client;
    protected $data;
       
    public function getData() {
        if (is_null($this->data)) {
            $this->load();
        }
        return $this->data;
    }
    
    public function getBaseUri() {
        return 'https://api.hh.ru';
    }
    
    public function setClient(Client $client) {
        $this->client = $client;
    }
    
    public function getClient() {
        return $this->client;
    }
    
    protected function getResponse() {
        return $this->client->request(
                $this->getUri(),
                $this->getMethod(),
                $this->getHeaders(),
                $this->getContent()
        );
    }
    
    public function load() {
        $response = $this->getResponse();
        $this->data = new EntityData(json_decode($response->getContent()));
    }

    public function getMethod() {
        return 'GET';
    }    
    
    public function getHeaders() {
        return [];
    }
    
    public function getContent() {
        return null;
    }
    
    abstract public function getUri();
}