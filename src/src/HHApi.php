<?php

class HHApp {

    protected $accessToken;
    
    protected static $_instance;

    protected function __construct(HHAccessToken $accessToken) {
        $this->accessToken = $accessToken;
    }

    public static function init(HHAccessToken $accessToken) {
        static::$_instance = new static($accessToken);
    }

    public static function intstance() {
        return static::$_instance;
    }

    public function authorizedRequest($url, $method, $content = null, array $headers = array()) {
        $headers['Authorization'] = 'Bearer ' . $this->getAccessToken()->access_token;
        $response = $this->request($url, $method, $content, $headers);
        if ($response->hasOldTokenError()) {
            $this->refreshAcessToken();
            $headers['Authorization'] = 'Bearer ' . $this->getAccessToken()->access_token;
            $response = $this->request($url, $method, $content, $headers);
        }
        return $response;
    }

    public function request($url, $method, $content = null, array $headers = array()) {
        $request = new HHRequest;
        $request->setMethod($method)
                ->setHeaders($headers)
                ->setContent($content);
        return $request->send($url);
    }
    
    public function getAccessToken() {
        return $this->accessToken;
    }
    
    protected function refreshAccessToken() {
        $auth = new HHAuth;
        $responseToken = $auth->getRefreshAccessTokenResponse($this->getAccessToken()->refresh_token);
        $accessToken = $auth->createAccessTokenFromResponse($responseToken);
        if ($accessToken === false) {
            throw new HHException('refresh token error', HHException::ERROR_EXTERNAL);
        }
        $this->accessToken = $accessToken;        
    }

}
