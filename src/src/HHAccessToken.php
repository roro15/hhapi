<?php

class HHAccessToken {

    /**
     * @var string
     */
    public $access_token;

    /**
     * @var string
     */
    public $refresh_token;

    public function __construct($data) {
        $this->access_token = $data['access_token'];
        $this->refresh_token = $data['refresh_token'];
    }

}
