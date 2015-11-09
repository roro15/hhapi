<?php

namespace exception;

class AuthException extends BaseException {
    public function __construct($message = '') {
        parent::__construct($message, static::ERROR_AUTH);
    }
}