<?php

namespace hh\base;
use Countable;
use Iterator;
use ArrayAccess;
use OutOfBoundsException;
use LogicException;

class Collection implements Countable, Iterator, ArrayAccess {
    
    protected $pagination;
    protected $query;
    protected $pages = array();
    protected $currentIndex = 0;
    
    public function __construct(Pagination $pagination, Query $query, array $models) {
        $this->pagination = $pagination;
        $this->query = clone $query;
        $this->pages[$pagination->getCurrentPage()] = $models;
    }
    
    public function getPagination() {
        return $this->pagination;
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
        return $this->offsetGet($this->currentIndex);
    }
    
    public function next() {
        $this->currentIndex++;
    }
    
    public function rewind() {
        $this->currentIndex = 0;
    }
    
    public function offsetGet($offset) {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException;
        }
        list($page, $index) = $this->indexToPage($offset);
        $models = $this->getPage($page);
        return $models[$index];
    }
    
    public function offsetSet($offset, $value) {
        throw new LogicException;
    }
    
    public function offsetExists($offset) {
        return $offset >= 0 && $offset <  $this->count();
    }
    
    public function offsetUnset($offset) {
        throw new LengthException;
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
        return array($page, $index);
    }
}