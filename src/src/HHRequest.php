<?php

class HHRequest {

    private $_method = 'GET';
    private $_content = '';
    private $_headers = array();

    public function getDefaultUserAgent() {
        return 'HHExport/1.0 (roro15@yandex.ru)';
    }

    private $_defaultCurlOptions = array(
    );

    public function hasHeader($header) {
        return isset($this->_headers[$header]);
    }

    public function setHeader($header, $value) {
        $this->_headers[$header] = $value;
        return $this;
    }
    
    public function setHeaders(array $headers) {
        foreach($headers as $header => $value) {
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
            throw new HHRequestException("Could not init curl", HHException::ERROR_SERVER);
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
            throw new HHRequestException("Error setting curl options: " . curl_error($ch), HHException::ERROR_SERVER);
        }
        HHLog::instance()->log("Request. Url:\n" . $url . "\nMethod:\n" . $method . "\nHeaders:\n" . implode("\n", $httpHeaders) . "\nContent:\n" . $content);
        $response = curl_exec($ch);
        if ($response === false) {
            throw new HHRequestException("Error executing curl: " . curl_error($ch), HHException::ERROR_NETWORK);
        } else {
            return new HHResponse($response);
        }
    }

}
