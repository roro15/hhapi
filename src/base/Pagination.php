<?php

namespace base;

class Pagination {
    protected $perPage;
    protected $totalCount;
    
    public function __construct($totalCount, $perPage = 20) {
        $this->totalCount = $totalCount;
        $this->perPage = $perPage;
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
}