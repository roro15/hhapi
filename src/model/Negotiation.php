<?php

namespace hh\model;
use hh\base\Model;

class Negotiation extends Model {
    protected $resume;
    
    public function getResume() {
        if (is_null($this->resume) && !empty($this->getRaw()->resume)) {
            $query = new ResumeQuery($this->getClient());
            $this->resume = $query
                    ->setAbsoluteUrl($this->getRaw()->resume->url)
                    ->one();
        }
        
        return $this->resume;
    }
}