<?php

namespace hh\base;

class Auth {
    
    protected $id;
    protected $secret;
    protected $redirectUrl;

    public function __construct($id, $secret, $redirectUrl) {
        $this->id = $id;
        $this->secret = $secret;
        $this->redirectUrl = $redirectUrl;
        $sessionId = session_id();
        if (empty($sessionId)) {
            session_start();
        }
        if (empty($_SESSION['hh_auth'])) {
            $_SESSION['hh_auth'] = [];
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getSecret() {
        return $this->secret;
    }

    public function getRedirectUrl() {
        return $this->redirectUrl;
    }

    public function getRefreshTokenUrl() {
        return 'https://hh.ru/oauth/token';
    }

    public function getAuthorizationUrl($force = false, $redirectUrl = null) {
        $clientId = $this->getId();
        if (is_null($redirectUrl)) {
            $redirectUrl = $this->getRedirectUrl();
        }
        $state = $this->generateState();

        $url = "https://hh.ru/oauth/authorize?"
                . "response_type=code&client_id={$clientId}&state={$state}&"
                . "redirect_uri={$redirectUrl}";
        if ($force) {
            $url .= '&force_login=true';
        }
        return $url;
    }

    public function getTokenResponse($authorizationCode, $redirectUrl = null) {
        if (is_null($redirectUrl)) {
            $redirectUrl = $this->getRedirectUrl();
        }
        $content = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->getId(),
            'client_secret' => $this->getSecret(),
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUrl,
        );
        return $this->sendRequest($this->getTokenUrl(), $content);
    }

    public function getRefreshTokenResponse($refreshToken) {
        $content = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        );
        return $this->sendRequest($this->getRefreshTokenUrl(), $content);
    }

    protected function sendRequest($url, $content) {
        $request = new Request;
        $request->setMethod('POST')
            ->setContent($content);
        return $request->send($url);
    }

    protected function generateState() {
        $state = mt_rand(100000, 999999);
        $_SESSION['hh_auth']['state'] = $state;
        return $state;
    }

    public function checkState() {
        if (isset($_GET['state']) && isset($_SESSION['hh_auth']['state']) && $_GET['state'] === $_SESSION['hh_auth']['state']) {
            return false;
        }
        return true;
    }

    public function createTokenFromResponse(Response $response) {
        $token = json_decode($response->getContent(), true);
        if (isset($token['access_token'])) {
            return new Token($token['access_token'], $token['response_token']);
        }
        return false;
    }
    
    
    public function refreshToken($refreshToken) {
        $response = $this->getRefreshTokenResponse($refreshToken);
        return $this->createTokenFromResponse($response);
    }
}
