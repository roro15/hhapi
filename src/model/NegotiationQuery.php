<?php

namespace hh\model;
use hh\base\Client;
use hh\base\Query;
use hh\model\Negotiation;

class NegotiationQuery extends Query {
    protected $negotiationCollectionName;
    
    public function __construct(Client $client, $negotiationCollectionName) {
        $this->negotiationCollectionName = $negotiationCollectionName;
        parent::__construct($client);
    }
    
    public function getUrl() {
        return 'negotiations/' . $this->negotiationCollectionName;
    }
    
    public function getModelClass() {
        return Negotiation::className();
    }
    
    public function byVacancyId($vacancyId) {
        return  $this->addQueryParam('vacancy_id', $vacancyId);
    }
    
    public function withUpdatesOnly() {
        return $this->addQueryParam('with_updates_only', 'true');
    }
}