<?php

class HHVacancyException extends HHException {
	public function __construct(array $errors) {
		$message = '';
		foreach($errors as $error) {
			$message .= $error;
		}
		parent::__construct($message, self::ERROR_VALIDATION);
	}
}