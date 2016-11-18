<?php

class PaystackConfig {
	private static $apiUsername;
	private static $apiPassword;
	private static $walletID;
	private static $directKitURL;
	private static $webkitURL;
	private static $directkitURLTest;
	private static $webkitURLTest;
	private static $env;
	private static $cssURL;

	private static function isTestMode() {
		return ("live" != self::$env);
	}

	//Setters
	public static function setAPIUsername($apiUsername) {
		self::$apiUsername = $apiUsername;
	}

	public static function setAPIPassword($apiPassword) {
		self::$apiPassword = $apiPassword;
	}

	public static function setWalletID($walletID) {
		self::$walletID = $walletID;
	}

	public static function setDirectkitURL($directKitURL) {
		self::$directKitURL = $directKitURL;
	}

	public static function setWebkitURL($webkitURL) {
		self::$webkitURL = $webkitURL;
	}

	public static function setDirectkitURLTest($directkitURLTest) {
		self::$directkitURLTest = $directkitURLTest;
	}

	public static function setWebkitURLTest($webkitURLTest) {
		self::$webkitURLTest = $webkitURLTest;
	}

	public static function setEnv($env) {
		self::$env = $env;
	}

	public static function setCSSURL($cssURL) {
		self::$cssURL = $cssURL;
	}

	//Getters
	public static function getApiUsername() {
		return self::$apiUsername;
	}
	
	public static function getApiPassword(){
		return self::$apiPassword;
	}

	public static function getWalletID() {
		return self::$walletID;
	}

	public static function getDirectkitURL() {
		if (self::isTestMode()) {
			$url = self::$directkitURLTest;
		}
		else {
			$url = self::$directKitURL;
		}

		return rtrim($url, '/');
	}

	//Getters
	public static function getWebkitURL() {
		if (self::isTestMode()) {
			$url = self::$webkitURLTest;
		}
		else {
			$url = self::$webKitURL;
		}

		return rtrim($url, '/');
	}

	public static function getCSSURL() {
		return self::$cssURL;
	}
}

require(dirname(__FILE__) . '/class.paystack-lwerror.php');
require(dirname(__FILE__) . '/class.paystack-api-response.php');
require(dirname(__FILE__) . '/class.paystack-kit.php');
?>