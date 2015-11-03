<?php

class HHApiSoapServer {

    /**
     * @param HHVacancy $vacancy
     * @param HHAccessToken $accessToken
     * @return HHSoapServerResponse
     */
    public function publishVacancy(HHVacancy $vacancy, HHAccesssToken $accessToken) {

        $apiClient = new HHApiClient;
        $response = new HHSoapServerResponse;
        try {
            list($vacancyId, $expiresAt, $newToken) = $apiClient->publishVacancy($vacancy, $accessToken);
            $response->vacancy_id = $vacancyId;
            $response->expires_at = $expiresAt;
            if ($newToken->access_token !== $accessToken->access_token) {
                $response->access_token = $newToken->access_token;
                $response->refresh_token = $newToken->refresh_token;
            }
        } catch (HHException $e) {
            HHLog::instance()->error($e);
            $response->error_message = $e->getCode();
        } catch (Exception $e) {
            $response->error_message = HHException::ERROR_SERVER;
        }
        return $response;
    }

    /**
     *
     * @param HHVacancy $vacancy
     * @param int $id
     * @param HHAccessToken $accessToken
     * @return HHSoapServerResponse
     */
    public function changeVacancy(HHVacancy $vacancy, $id, HHAccessToken $accessToken) {
        $apiClient = new HHApiClient;
        $response = new HHSoapServerResponse;
        try {
            list($expiresAt, $newToken) = $apiClient->changeVacancy($id, $vacancy, $accessToken);
            if ($newToken->access_token !== $accessToken->access_token) {
                $response->access_token = $newToken->access_token;
                $response->refresh_token = $newToken->refresh_token;
            }
            $response->vacancy_id = $id;
            $response->expires_at = $expiresAt;
        } catch (HHException $e) {
            HHLog::instance()->error($e);
            $response->error_message = $e->getCode();
        } catch (Exception $e) {
            $response->error_message = HHException::ERROR_SERVER;
        }
        return $response;
    }

    /**
     * @param int $id
     * @param HHAccessToken $accessToken
     * @return HHSoapServerResponse
     */
    public function archiveVacancy(int $id, HHAccessToken $accessToken) {
        $apiClient = new HHApiClient;
        $response = new HHSoapServerResponse;
        try {
            $newToken = $apiClient->archiveVacancy($id, $accessToken);
            if ($newToken->access_token !== $accessToken->access_token) {
                $response->access_token = $newToken->access_token;
                $response->refresh_token = $newToken->refresh_token;
            }
            $response->vacancy_id = $id;
        } catch (HHException $e) {
            HHLog::instance()->error($e);
            $response->error_message = $e->getCode();
        } catch (Exception $e) {
            $response->error_message = HHException::ERROR_SERVER;
        }
        return $response;
    }

}
