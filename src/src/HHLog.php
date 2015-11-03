<?php

class HHLog {

	private static $_instance = null;

	public function getLogFilePath() {
		return '/home/hydroschoo/hydroschool.ru/docs/hh.log';
	}

	private function __construct() {

	}

	public static function instance() {
		if (empty(self::$_instance)) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	public function log($message) {
		
		$date = date('Y-m-d H:i:s');
		$path = $this->getLogFilePath();
		if (file_exists($path)) {
			$content = file_get_contents($path);
		} else {
			$content = '';
		}
		if (!empty($content)) {
			$content .= PHP_EOL . PHP_EOL;
		}
		$content .= $date . PHP_EOL . $message;
		file_put_contents($path, $content);
	}

	public function error(HHException $e) {
		$message = "Error occured.\nCode:\n"
			. $e->getCode()
			. "Message:\n"
			. $e->getMessage();
		$this->log($message);
	}
}