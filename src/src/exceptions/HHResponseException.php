<?php

class HHResponseException extends HHException {

	private $_request;
	private $_response;
	private $_name;

	public function __construct($name, $url, HHRequest $request, HHResponse $response, $code = self::ERROR_UNKNOWN) {
		$message = "Error {$name}.\n"
			. "Url: {$url}.\n"
			. "Request headers:\n"
			. implode("\n", $request->getHeaders()) . "\n"
			. "Request content:\n"
			. $request->getContent() . "\n"
			. "Response status:\n"
			. $response->getStatusCode() . "\n"
			. "Response headers:\n"
			. implode("\n", $response->getHeaders()) . "\n"
			. "Response content:\n"
			. $response->getContent() . "\n";
		if ($response->hasAuthError()) {
			$code = self::ERROR_AUTH;
		}
		parent::__construct($message, $code);
	}

	public function getRequest() {
		return $this->_request;
	}

	public function getResponse() {
		return $this->_response;
	}

	public function getName() {
		return $this->_name;
	}
}