<?php

use ArrayAccess;

class HHNegotiationList implements ArrayAccess, IteratorAggregate, Countable {

    protected $pages = [];
    protected $pagination;
    protected $collection;

    public function __construct(HHNegotiationCollection $collection) {
        $this->collection = $collection;
        $this->getPage();
    }
    
    public function getPage($index = 0) {
        $url = $this->buildUrl();
    }
    
    public function count() {
        
    }
    
    protected function buildUrl() {
        return $this->collection->getUrl();
    }
}
