<?php

namespace hh\model;
use hh\base\Query;
use hh\base\Client;

class NegotiationMessageQuery extends Query {
    
    protected $negotiationId;
    
    public function __construct(Client $client, $negotiationId) {
        $this->negotiationId = $negotiationId;
        parent::__construct($client);
    }
    
    public function getModelClass() {
        NegotiationMessage::className();
    }
    
    public function getUrl() {
        return '/negotiations/' . $this->negotiationId . '/messages';
    }

}
