<?php

namespace hh\base;

class Response {

    private $_code;
    private $_headers;
    private $_content;
    private $_response;
    private $_parsed;
    private $_request;

    public function __construct($response, Request $request) {
        $this->_response = $response;
        $info = self::parseResponse($response);
        $this->_code = $info['code'];
        $this->_headers = $info['headers'];
        $this->_content = $info['content'];
        $this->_parsed = json_decode($this->_content);
        $this->_request = $request;
    }

    public function getStatusCodes() {
        return array(
            200 => 'OK',
            204 => 'No Content',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
        );
    }

    public static function parseResponse($response) {
        $parts = explode("\r\n\r\n", $response);
        $contentText = array_pop($parts);
        $headersText = array_pop($parts);
        $headersList = explode("\n", $headersText);
        $statusText = array_shift($headersList);
        list($protocolText, $code, $codeText) = explode(" ", $statusText, 4);
        list($protocol, $version) = explode('/', $protocolText, 2);
        $headers = array();
        foreach ($headersList as $headerText) {
            list($header, $value) = explode(':', $headerText);
            $headers[$header] = trim($value);
        }
        return array(
            'code' => intval($code),
            'headers' => $headers,
            'content' => $contentText,
        );
    }

    public function getStatusCode() {
        return $this->_code;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function hasHeader($header) {
        return array_key_exists($header, $this->_headers);
    }

    public function getHeader($header) {
        return $this->hasHeader($header) ? $this->_headers[$header] : null;
    }

    public function getResponse() {
        return $this->_response;
    }

    public function getContent() {
        return $this->_content;
    }

    public function hasError() {
        return $this->_code !== 200;
    }

    public function getParsed() {
        return $this->_parsed;
    }

    protected function getOAuthError() {
        if ($this->getStatusCode() !== 403) {
            return false;
        }
        $parsed = $this->getParsed();
        $errors = $parsed->errors;
        if (empty($errors)) {
            return false;
        }
        foreach ($errors as $error) {
            $error->type === 'ouath';
            return $error->value;
        }
        return false;
    }

    public function hasOldTokenError() {
        return $this->getOAuthError() === 'token_expired';
    }

    public function hasAuthError() {
        return $this->getOAuthError() === 'bad_authorization';
    }
    
    public function getRequest() {
        return $this->_request;
    }

}
