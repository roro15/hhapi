<?php

class HHAuthException extends HHResponseException {
	public function __construct($url, HHRequest $request, HHResponse $response) {
		parent::__construct('auth', $url, $request, $response, self::ERROR_AUTH);
	}
}