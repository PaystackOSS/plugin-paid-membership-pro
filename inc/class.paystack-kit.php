<?php
class PaystackKit {

	private static function getConfig() {
		return array (
			'directKitUrl' => PaystackConfig::getDirectkitUrl(),
			'webkitUrl' => PaystackConfig::getWebkitUrl(),
			'wlLogin' => PaystackConfig::getApiUsername(),
			'wlPass' => PaystackConfig::getApiPassword(),
			'language' => 'fr'
			);
	}

	private function printDirectkitInput($string){
		if (self::$printInputAndOutputXml){
			print '<br/>DEBUG INTPUT START<br/>';
			echo htmlentities($string);
			//$xml = new SimpleXMLElement($string); echo $xml->asXML();
			print '<br/>DEBUG INTPUT END<br/>';
		}
	}

	private function sendRequest($methodName, $params, $version){
		$accessConfig = self::getConfig();

		$ua = (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : '';	
		$ip = (isset($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : '';


		$xml_soap = '<?xml version="1.0" encoding="utf-8"?><soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope"><soap12:Body><'.$methodName.' xmlns="Service_mb">';

		foreach ($params as $key => $value) {
			$xml_soap .= '<'.$key.'>'.$value.'</'.$key.'>';
		}

		$xml_soap .= '<version>'.$version.'</version>';
		$xml_soap .= '<wlPass>'.$accessConfig['wlPass'].'</wlPass>';
		$xml_soap .= '<wlLogin>'.$accessConfig['wlLogin'].'</wlLogin>';
		$xml_soap .= '<language>'.$accessConfig['language'].'</language>';
		$xml_soap .= '<walletIp>'.$ip.'</walletIp>';
		$xml_soap .= '<walletUa>'.$ua.'</walletUa>';
		$xml_soap .= '</'.$methodName.'></soap12:Body></soap12:Envelope>';

		var_dump ('<br/>DEBUG INTPUT START<br/>'.htmlentities($xml_soap).'<br/>DEBUG INTPUT END<br/>');

		$headers = array("Content-type: text/xml;charset=utf-8",
						"Accept: application/xml",
						"Cache-Control: no-cache",
						"Pragma: no-cache",
						'SOAPAction: "Service_mb/'.$methodName.'"',
						"Content-length: ".strlen($xml_soap),
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $accessConfig['directKitUrl']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_soap);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$response = curl_exec($ch);

		if(curl_errno($ch))
		{
			throw new Exception(curl_error($ch));
			
		} else {
			$returnCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			switch($returnCode){
				case 200:
					//General parsing
					$response = html_entity_decode($response);
					
					$response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $response);
					$response = str_replace('xmlns="Service_mb"', '', $response); //suppress absolute uri warning
					$xml = new SimpleXMLElement($response);
					$content = $xml->soapBody->{$methodName.'Response'}->{$methodName.'Result'};
					
					return new ApiResponse($content);
				case 400:
					throw new Exception("Bad Request : The server cannot or will not process the request due to something that is perceived to be a client error", 400);
					break;
				case 403:
					throw new Exception("IP is not allowed to access Lemon Way's API, please contact support@lemonway.fr", 403);
					break;
				case 404:
					throw new Exception("Check that the access URLs are correct. If yes, please contact support@lemonway.fr", 404);
					print "Check that the access URLs are correct. If yes, please contact support@lemonway.fr";
					break;
				case 500:
					throw new Exception("Lemon Way internal server error, please contact support@lemonway.fr", 500);
					break;
				default:
					break;
			}
			throw new Exception("HTTP CODE IS NOT SUPPORTED ", $returnCode);
			die();
		}
	}

	public function MoneyInWebInit($params) {
		return self::sendRequest('MoneyInWebInit', $params, '1.3');
	}

	public function RegisterCard($params) {
		return self::sendRequest('RegisterCard', $params, '1.1');
	}
	
	public function GetMoneyInTransDetails($params) {
		$res = self::sendRequest('GetMoneyInTransDetails', $params, '1.8');
		if (!isset($res->lwError)){
			$res->operations = array();
			foreach ($res->lwXml->TRANS->HPAY as $HPAY){
				$res->operations[] = new Operation($HPAY);
			}
		}
		return $res;
	}
}
?>