<?php

namespace entity;
use base\Client;

class Resume extends SecureEntity {
    
    protected $uri;
    
    public function __construct(Client $client, $uri) {
        $this->setClient($client);
        $this->uri = $uri;
    }
    
    public function getUri() {
        return $this->uri;
    }

}