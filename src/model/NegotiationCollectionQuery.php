<?php

namespace hh\model;
use hh\base\Query;
use hh\base\Client;

class NegotiationCollectionQuery extends Query {
    
    public function __construct(Client $client) {
        parent::__construct($client);
        $this->setMultipleResponseAttribtue('states');
    }
    
    public function getUrl() {
        return 'negotiations';
    }
    
    public function getModelClass() {
        return NegotiationCollection::className();
    }
    
    public function byVacancyId($vacancyId) {
        return  $this->addQueryParam('vacancy_id', $vacancyId);
    }
    
}
