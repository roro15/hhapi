<?php

namespace hh\exception;

use hh\base\Response;

class ResponseException extends BaseException {

    protected $request;
    protected $response;

    public function __construct($name, $code, Response $response) {
        $this->request = $response->getRequest();
        $this->response = $response;
        $message = $this->buildMessage($name);

        if ($response->hasAuthError()) {
            $code = self::ERROR_AUTH;
        }
        parent::__construct($message, $code);
    }

    protected function buildMessage($name) {
        $request = $this->getRequest();
        $response = $this->getResponse();
        return "Error {$name}.\n"
                . "Request headers:\n"
                . implode("\n", $request->getHeaders()) . "\n"
                . "Request content:\n"
                . $request->getContent() . "\n"
                . "Response status:\n"
                . $response->getStatusCode() . "\n"
                . "Response headers:\n"
                . implode("\n", $response->getHeaders()) . "\n"
                . "Response content:\n"
                . $response->getContent() . "\n";
    }

    public function getRequest() {
        return $this->request;
    }
    
    public function getResponse() {
        return $this->response;
    }

}
