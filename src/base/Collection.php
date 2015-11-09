<?php

namespace hh\base;
use Countable;
use Iterator;

class Collection implements Countable, Iterator {
    
    protected $pagination;
    protected $query;
    protected $pages = [];
    protected $currentIndex = 0;
    
    public function __construct(Pagination $pagination, Query $query, array $models) {
        $this->pagination = $pagination;
        $this->query = clone $query;
        $this->pages[$pagination->getCurrentPage()] = $models;
    }
    
    public function getPage($index) {
        if ($index < 0 || $index >=  $this->pagination->getPageCount()) {
            return false;
        }
        if (!isset($this->pages[$index])) {
            $this->loadPage($index);
        }
        return $this->pages[$index];
    }
    
    public function count() {
        return $this->pagination->getTotalCount();
    }
    
    public function key() {
        return $this->currentIndex;
    }
    
    public function valid() {
        return $this->currentIndex < $this->count();
    }
    
    public function current() {
        list($page, $index) = $this->indexToPage($this->currentIndex);
        $models = $this->getPage($page);
        return $models[$index];
    }
    
    public function next() {
        $this->currentIndex++;
    }
    
    public function rewind() {
        $this->currentIndex = 0;
    }
    
    protected function loadPage($index) {
        $this->pages[$index] = $this->query
                ->setPage($index)
                ->models();
    }
    
    protected function indexToPage($index) {
        $perPage = $this->pagination->getPerPage();
        $page = floor($index / $perPage);
        $index %= $perPage;
        return [$page, $index];
    }
}