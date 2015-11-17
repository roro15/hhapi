<?php

namespace hh\model;
use hh\base\Query;
use hh\base\Client;

class NegotiationMessageQuery extends Query {

    protected $url;
    
    public function __construct(Client $client, $url) {
        $this->url = $url;
        parent::__construct($client);
    }
    
    public function getModelClass() {
        NegotiationMessage::className();
    }
    
    public function getUrl() {
        return $this->url;
    }

}
