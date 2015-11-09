<?php

namespace hh\model;
use hh\base\Query;
use hh\model\Resume;

class ResumeQuery extends Query {
    public function getUrl() {
        return 'resumes';
    }
    
    public function getModelClass() {
        return Resume::className();
    }
}