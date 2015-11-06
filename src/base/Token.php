<?php

namespace base;

class Token {
    
    public $accessToken;
    public $refreshToken;
    
    public function __construct($accessToken, $refreshToken) {
        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
    }
}