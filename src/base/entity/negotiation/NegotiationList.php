<?php

namespace negotiation;
use entity\negotiation\NegotiationCollection;
use base\Pagination;

class NegotiationList {
    
    protected $collection;
    protected $items = [];
    protected $pagination;
    protected $perPage;
    protected $pagesLoaded = [];
    
    public function __construct(Client $client, NegotiationCollection $collection, $perPage = 20) {
        $this->setClient($client);
        $this->collection = $collection;
        $this->perPage = $perPage;
        $this->load();
    }
    
    public function getUri() {
        return $this->collection->getUri();
    }
    
    public function getPage($page) {
        if ($page < 0 || $page >= $this->pagination->getPageCount()) {
            throw \Exception;
        }
        if (!$this->isPageLoaded($page)) {
            $this->load($page);
        }
        return $this->getPageItems($page);
    }
    
    protected function getPageItems($page) {
        $count = $this->pagination->getPerPage();
        $totalCount = $this->pagination->getTotalCount();
        $items = [];
        for($index =  $count * $page; $index < $totalCount && $index < $count + $perPage; $index++) {
            $items[] = $this->items[$index];
        }
        return $items;
    }
    
    protected function isPageLoaded($page) {
        return array_key_exists($page, $this->pagesLoaded);
    }
    
    protected function load($page = 0) {
        $response = $this->client($this->getUri());
        $content = json_decode($response->getContent());
        $index = $page * $this->perPage;
        
        foreach($content->items as $item) {
            $this->items[$index++] = new Negotiation($this->client, $item);
        }
        $this->pagesLoaded[$page] = true;
    }
    
    protected function buildPagination($data) {
        return new Pagination($data->found, $this->perPage);
    }
    
    
}