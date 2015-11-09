<?php

namespace hh\base;

class Pagination {
    protected $perPage;
    protected $totalCount;
    protected $currentPage;
    
    public function __construct($totalCount, $perPage = 20, $currentPage = 0) {
        $this->totalCount = $totalCount;
        $this->perPage = $perPage;
        $this->currentPage = $currentPage;
    }
    
    public function getPerPage() {
        return $this->perPage;
    }
    
    public function setPerPage($perPage) {
        $this->perPage = $perPage;
    }
    
    public function getTotalCount() {
        return $this->totalCount;
    }
    
    public function setTotalCount($totalCount) {
        $this->totalCount = $totalCount;
    }
    
    public function getPageCount() {
        return ceil($this->totalCount / $this->perPage);
    }
    
    public function getCurrentPage() {
        return $this->currentPage;
    }
}