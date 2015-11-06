<?php

namespace base;
use exception\AuthException;

class Client {
    
    protected $appId;
    protected $appSecret;
    protected $token;
    
    public function __construct($appId, $appSecret, Token $token = null) {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
        $this->token = $token;
    }
    
    public function request($uri, $method, array $headers = [], $content = null) {
        $request = new Request;
        $headers['User-Agent'] = $this->buildUserAgent();
        $request->setMethod($method)
                ->setHeaders($headers)
                ->setContent($content);
        return $request->send($uri);
    }
    
    public function secureRequest($uri, $method, array $headers = [], $content = null) {
        if (empty($this->token)) {
            throw new AuthException;
        }
        $headers['Authorization'] = 'Bearer ' . $this->token->accessToken;
        $response = $this->request($uri, $method, $headers, $content);
        if ($response->hasOldTokenError()) {
            $this->refreshToken();
            $headers['Authorization'] = 'Bearer ' . $this->token->accessToken;
            $response = $this->request($uri, $method, $headers, $content);
        }
        return $response;
        
    }
    
    protected function refreshToken() {
        $content = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->token->refreshToken,
        ];
        $headers = [];
        $request = $this->prepareRequest('POST', $headers, $content);
        $response = $request->send($this->getRefreshTokenUri());
        $token = json_decode($response->getContent());
        if (!isset($token['access_token'])) {
            throw new AuthException;
        }
        $this->token = new Token($token['access_token'], $token['refresh_token']);
    }
    
    protected function buildUserAgent() {
        return 'HHExport/1.0 (roro15@yandex.ru)';
    }
    
    
}