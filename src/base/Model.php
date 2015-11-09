<?php

namespace hh\base;
use hh\exception\BaseException;

class Model {
    
    protected $client;
    protected $raw;
    
    public function __construct(Client $client, $raw) {
        $this->client = $client;
        $this->raw = $raw;
    }
    
    public function getRaw() {
        return $this->raw;
    }
    
    public function getClient() {
        return $this->client;
    }
    
    static public function getUrl() {
        throw new BaseException('Method should be redeclared.', BaseException::ERROR_SERVER);
    }
    
    public static function className() {
        return get_called_class();
    }
    
}