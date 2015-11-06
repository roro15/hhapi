<?php

namespace negotiation;
use base\Client;

class NegotiationCollectionList {
    
    protected $client;
    protected $vacancyId;
    protected $collections;
    
    public function __construct(Client $client, $vacancyId) {
        $this->client = $client;
        $this->vacancyId = $vacancyId;
    }
    
    public function getCollections() {
        
    }
    
    public function getCollectionByName($name) {
        $collections = $this->getCollections();
        return isset($collections[$name]) ? $collections[$name] : null;
    }
}