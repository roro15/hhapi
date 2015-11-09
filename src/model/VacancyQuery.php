<?php

namespace hh\model;
use hh\base\Query;

class VacancyQuery extends Query {
    public function getUrl() {
        return 'vacancies';
    }
    
    public function getModelClass() {
        return Vacancy::className();
    }
}
