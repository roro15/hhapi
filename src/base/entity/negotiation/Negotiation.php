<?php

namespace negotiation;
use base\Client;
use entity\Resume;

class Negotiation {
    
    protected $client;
    protected $raw;
    protected $resume;
    
    public function __construct(Client $client, $raw) {
        $this->client = $client;
        $this->raw = $raw;
    }
    
    public function getResume() {
        if (is_null($this->resume)) {
            $this->resume = new Resume($this->client, $this->raw->resume->url);
        }
        return $this->resume;
    }
}