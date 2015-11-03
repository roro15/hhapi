<?php

class HHAuth {

    const IBLOCK_ID = 19;

    private $_propertiesList;

    public function __construct() {
        $sessionId = session_id();
        if (empty($sessionId)) {
            session_start();
        }
        if (empty($_SESSION['hh_auth'])) {
            $_SESSION['hh_auth'] = array();
        }
    }

    public function getClientId() {
        return 'QUVA7G9JSELC30GOO01MAMP70ARJ4AEUQ5B5OIU0V9MPLA6O8NBRG0T1RL7RNE1R';
    }

    public function getClientSecret() {
        return 'KBPOV6C150Q6NN6O4C5DBSR59U7IUQ7U73A6QPETUVJ8724DCDCPEIRV785M5RVD';
    }

    public function getRedirectUri() {
        return 'http://' . $_SERVER['HTTP_HOST'] . '/test/hh.php';
    }

    public function getAccessTokenUrl() {
        return 'https://hh.ru/oauth/token';
    }

    public function getAuthorizationUrl($force = false, $redirectUri = null) {
        $clientId = $this->getClientId();
        if (is_null($redirectUri)) {
            $redirectUri = $this->getRedirectUri();
        }
        $state = $this->_generateState();

        $url = "https://hh.ru/oauth/authorize?"
                . "response_type=code&client_id={$clientId}&state={$state}&"
                . "redirect_uri={$redirectUri}";
        if ($force) {
            $url .= '&force_login=true';
        }
        return $url;
    }

    public function getAccessTokenResponse($authorizationCode, $redirectUri = null) {
        if (is_null($redirectUri)) {
            $redirectUri = $this->getRedirectUri();
        }
        $content = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret(),
            'code' => $authorizationCode,
            'redirect_uri' => $redirectUri,
        );
        return $this->_sendRequest($this->getAccessTokenUrl(), $content);
    }

    public function getRefreshAccessTokenResponse($refreshToken) {
        $content = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        );
        return $this->_sendRequest($this->getAccessTokenUrl(), $content);
    }

    private function _sendRequest($url, $content) {
        $request = new HHRequest;
        $request->setMethod('POST');
        $request->setContent($content);
        return $request->send($url);
    }

    private function _generateState() {
        $state = mt_rand(100000, 999999);
        $_SESSION['hh_auth']['state'] = $state;
        return $state;
    }

    public function checkState() {
        if (isset($_GET['state']) && isset($_SESSION['hh_auth']['state']) && $_GET['state'] === $_SESSION['hh_auth']['state']) {
            return false;
        }
    }

    public function createAccessTokenFromResponse(HHResponse $response) {
        $accessToken = json_decode($response->getContent(), true);
        if (isset($accessToken['access_token'])) {
            return new HHAccessToken($accessToken);
        }
        return false;
    }

    public function getIBlockSection($id) {
        $arFilter = array(
            'ID' => $id,
            'IBLOCK_ID' => self::IBLOCK_ID,
        );
        $arSelect = array('IBLOCK_ID', 'ID', 'UF_ACCESS_TOKEN', 'UF_REFRESH_TOKEN', 'UF_ORG_TYPE_COMPANY');
        $arNavStartParams = array('nTopCount' => 1);

        $dbResult = CIBlockSection::GetList(array(), $arFilter, false, $arSelect, $arNavStartParams);
        return $dbResult->GetNextElement();
    }

    public function loadAccessToken($id) {
        $section = $this->getIBlockSection($id);
        if (empty($section)) {
            return false;
        }
        if (!$section['UF_ACCESS_TOKEN']) {
            return false;
        }
        return new HHAccessToken(array(
            'access_token' => $section['UF_ACCESS_TOKEN'],
            'refresh_token' => $section['UF_REFRESH_TOKEN'],
        ));
    }

    public function saveAccessToken($id, HHAccessToken $accessToken) {
        $arFields = array(
            'UF_ACCESS_TOKEN' => $accessToken->accessToken,
            'UF_REFRESH_TOKEN' => $accessToken->refreshToken,
        );
        $section = new CIBlockSection;
        $section->Update($id, $arFields);
    }

}
