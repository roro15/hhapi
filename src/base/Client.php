<?php

namespace hh\base;
use hh\exception\AuthException;
use hh\exception\BaseException;
use hh\exception\ResponseException;

class Client {
    
    protected $name;
    protected $email;
    protected $version;
    protected $token;
    protected $auth;
    
    public function __construct($name, $email, $version = '1.0', Token $token = null, Auth $auth = null) {
        $this->name = $name;
        $this->email = $email;
        $this->version = $version;
        $this->token = $token;
        $this->auth = $auth;
    }
    
    public function getBaseUrl() {
        return 'https://api.hh.ru/';
    }
    
    public function request($url, $method, array $headers = [], $content = null) {
        $request = new Request;
        $headers['User-Agent'] = $this->buildUserAgent();
        $request->setMethod($method)
                ->setHeaders($headers)
                ->setContent($content);
        return $request->send($url);
    }
    
    public function secureRequest($url, $method, array $headers = [], $content = null) {
        if (empty($this->token)) {
            throw new AuthException;
        }
        $headers['Authorization'] = 'Bearer ' . $this->token->accessToken;
        $response = $this->request($url, $method, $headers, $content);
        if ($response->hasOldTokenError()) {
            $this->refreshToken();
            $headers['Authorization'] = 'Bearer ' . $this->token->accessToken;
            $response = $this->request($url, $method, $headers, $content);
        }
        return $response;
        
    }
    
    protected function refreshToken() {
        if (empty($this->auth)) {
            throw new BaseException('No auth specified');
        }
        $response = $this->auth->getRefreshTokenResponse($this->token->refreshToken);
        $content = $response->getParsed();
        if (!empty($content['access_token'])) {
            $this->token = new Token($content['access_token'], $content['refresh_token']);
        } else {
           throw new ResponseException('refresh token error', BaseException::ERROR_AUTH, $response);
        }
    }
    
    protected function buildUserAgent() {
        return $this->name . '/' . $this->version . ' (' . $this->email . ')';
    }

}