<?php

namespace hh\base;

use hh\exception\BaseException;

class Request {

    private $_method = 'GET';
    private $_content = '';
    private $_headers = array();

    public function getDefaultUserAgent() {
        return 'HHExport/1.0 (roro15@yandex.ru)';
    }

    public function hasHeader($header) {
        return isset($this->_headers[$header]);
    }

    public function setHeader($header, $value) {
        $this->_headers[$header] = $value;
        return $this;
    }

    public function setHeaders(array $headers) {
        foreach ($headers as $header => $value) {
            $this->setHeader($header, $value);
        }
        return $this;
    }

    public function getHeader($header) {
        return $this->hasHeader($header) ?
                $this->_headers[$header] : null;
    }

    public function getHeaders() {
        return $this->_headers;
    }

    public function getMethod() {
        return $this->_method;
    }

    public function setMethod($method) {
        $this->_method = $method;
        return $this;
    }

    public function getContent() {
        return $this->_content;
    }

    public function setContent($content) {
        $this->_content = $content;
        return $this;
    }

    public function send($url) {
        $ch = curl_init();
        if (!$ch) {
            throw new BaseException("Could not init curl", BaseException::ERROR_SERVER);
        }
        $method = $this->getMethod();
        $options = array(
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method,
        );

        $headers = $this->getHeaders();
        $content = $this->getContent();

        if (!is_null($content)) {
            if (is_array($content) || is_object($content)) {
                $content = http_build_query($content);
                $headers['Content-Type'] = 'application/x-www-form-urlencoded';
            } else {
                $headers['Content-Type'] = 'application/json';
            }
            $headers['Content-Length'] = mb_strlen($content);
            $options[CURLOPT_POSTFIELDS] = $content;
        }

        if (empty($headers['User-Agent'])) {
            $headers['User-Agent'] = $this->getDefaultUserAgent();
        }

        $httpHeaders = array();
        foreach ($headers as $header => $value) {
            $httpHeaders[] = "{$header}: {$value}";
        }
        $options[CURLOPT_HTTPHEADER] = $httpHeaders;

        if (!curl_setopt_array($ch, $options)) {
            throw new BaseException("Error setting curl options: " . curl_error($ch), BaseException::ERROR_SERVER);
        }
        $response = curl_exec($ch);
        if ($response === false) {
            throw new BaseException("Error executing curl: " . curl_error($ch), BaseException::ERROR_NETWORK);
        } else {
            return new Response($response, $this);
        }
    }

}
