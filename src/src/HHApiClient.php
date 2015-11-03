<?php

class HHApiClient {

    public function getApiBaseUrl() {
        return 'https://api.hh.ru/';
    }

    public function getCreateVacancyUrl() {
        return $this->getApiBaseUrl() . 'vacancies/?beta';
    }

    public function getVacancyInfo($vacancyId, HHAccessToken $accessToken) {
        $request = new HHRequest;
        $url = $this->getApiBaseUrl() . 'vacancies/' . $vacancyId;
        $request
                ->setMethod('GET')
                ->setHeader('Authorization', 'Bearer ' . $accessToken->access_token);
        list($response, $accessToken) = $this->_sendRequestWithRefreshAcessToken($request, $url, $accessToken);
        if ($response->getStatusCode() !== 200) {
            throw new HHResponseException('get vacancy info', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }
        $vacancyInfo = $response->getParsed();
        return array($vacancyInfo, $accessToken);
    }

    public function getAddressList(HHAccessToken $accessToken) {
        list($me, $accessToken) = $this->getMe($accessToken);
        $url = $this->getApiBaseUrl() . 'employers/' . $me->employer->id . '/addresses';
        $request = new HHRequest;
        $request
                ->setMethod('GET')
                ->setHeader('Authorization', 'Bearer ' . $accessToken->access_token);
        list($response, $accessToken) = $this->_sendRequestWithRefreshAcessToken($request, $url, $accessToken);
        if ($response->getStatusCode() === 200) {
            return array($response->getParsed(), $accessToken);
        } else {
            throw new HHResponseException('get address list', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }
    }

    public function getFirstAddressId(HHAccessToken $accessToken) {
        list($addressList, $accessToken) = $this->getAddressList($accessToken);
        if (!empty($addressList->items)) {
            return array($addressList->items[0]->id, $accessToken);
        }
        return array(null, $accessToken);
    }

    public function publishVacancy(HHVacancy $vacancy, HHAccessToken $accessToken) {
        $url = $this->getCreateVacancyUrl();
        if (empty($vacancy->address_id)) {
            list($addressId, $accessToken) = $this->getFirstAddressId($accessToken);
            $vacancy->address_id = $addressId;
        }
        $content = HHVacancy::toJSON($vacancy);
        $request = new HHRequest;
        $request->setMethod('POST')
                ->setHeader('Authorization', 'Bearer ' . $accessToken->access_token)
                ->setContent($content);
        list($response, $accessToken) = $this->_sendRequestWithRefreshAcessToken($request, $url, $accessToken);

        if ($response->getStatusCode() !== 201) {
            throw new HHResponseException('publish vacancy', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }

        $vacancyUrl = $response->getHeader('Location');
        $vacancyId = $this->_parseVacancyId($vacancyUrl);

        if ($vacancyId === false) {
            throw new HHResponseException('bad vacancy id', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }

        list($expiresAt, $accessToken) = $this->getExpiresAt($vacancyId, $accessToken);

        return array(
            $vacancyId,
            $expiresAt,
            $accessToken,
        );
    }

    public function getExpiresAt($vacancyId, HHAccessToken $accessToken) {
        $expiresAt = null;
        try {
            list($vacancyInfo, $accessToken) = $this->getVacancyInfo($vacancyId, $accessToken);
            if ($vacancyInfo instanceof stdClass && isset($vacancyInfo->expires_at)) {
                $dt = new DateTime($vacancyInfo->expires_at);
                $expiresAt = $dt->format('Y-m-d');
            }
        } catch (HHException $e) {
            
        }
        return array($expiresAt, $accessToken);
    }

    public function archiveVacancy($id, HHAccessToken $accessToken) {
        list($me, $accessToken) = $this->getMe($accessToken);
        $url = $this->getApiBaseUrl() . 'employers/' . $me->employer->id . '/vacancies/archived/' . $id;
        $request = new HHRequest;
        $request
                ->setMethod('PUT')
                ->setHeader('Authorization', 'Bearer ' . $accessToken->access_token);
        list($response, $accessToken) = $this->_sendRequestWithRefreshAcessToken($request, $url, $accessToken);
        if ($response->getStatusCode() === 204) {
            return array($accessToken->access_token, $accessToken->refresh_token);
        } else {
            throw new HHResponseException('archive vacancy', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }
    }

    public function changeVacancy($id, HHVacancy $vacancy, HHAccessToken $accessToken) {
        $url = $this->getApiBaseUrl() . 'vacancies/' . $id;
        $request = new HHRequest;
        $request
                ->setMethod('PUT')
                ->setHeader('Authorization', 'Bearer ' . $accessToken->access_token)
                ->setContent(HHVacancy::toJSON($vacancy, true));
        list($response, $accessToken) = $this->_sendRequestWithRefreshAcessToken($request, $url, $accessToken);
        if ($response->getStatusCode() !== 204) {
            throw new HHResponseException('change vacancy', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }
        list($expiresAt, $accessToken) = $this->getExpiresAt($id, $accessToken);
        return array($expiresAt, $accessToken);
    }

    public function getMe(HHAccessToken $accessToken) {
        $url = $this->getApiBaseUrl() . 'me';
        $request = new HHRequest();
        $request->setHeader('Authorization', 'Bearer ' . $accessToken->access_token);
        list($response, $accessToken) = $this->_sendRequestWithRefreshAcessToken($request, $url, $accessToken);
        if ($response->getStatusCode() === 200) {
            return array($response->getParsed(), $accessToken);
        }
        throw new HHResponseException('request personal data', $url, $request, $response, HHException::ERROR_EXTERNAL);
    }

    public function _refreshAcessToken($refreshToken) {
        $auth = new HHAuth;
        $responseToken = $auth->getRefreshAccessTokenResponse($refreshToken);
        $accessToken = $auth->createAccessTokenFromResponse($responseToken);
        if ($accessToken === false) {
            throw new HHResponseException('refresh token error', $url, $request, $response, HHException::ERROR_EXTERNAL);
        }
        return $accessToken;
    }

    private function _parseVacancyId($vacancyUrl) {
        $pattern = '/.*\/(\d+)\/?$/';
        $matches = array();
        if (preg_match($pattern, $vacancyUrl, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function _sendRequestWithRefreshAcessToken(HHRequest $request, $url, HHAccessToken $accessToken) {
        $response = $request->send($url);
        if ($response->hasOldTokenError()) {
            $accessToken = $this->_refreshAcessToken($accessToken->refresh_token);
            $response = $request
                    ->setHeader('Authorization', 'Bearer ' . $accessToken->access_token)
                    ->send($url);
        }
        return array($response, $accessToken);
    }

}
