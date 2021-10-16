<?php

class Idpprovider_library {

	// Defining some trusted Service Providers.
	private $trusted_sps = [
		'urn:localhost:81'=>'http://localhost:81/wp/wp-login.php',
		'urn:portal.tubers.uk'=>'http://portal.tubers.uk/wp-login.php',
		'urn:coordinate.tubers.uk'=>'http://coordinate.tubers.uk/wp-login.php',
		'urn:tubers.uk'=>'https://tubers.uk/wp-login.php',

	];

	/**
	 * Retrieves the Assertion Consumer Service.
	 *
	 * @param string
	 *   The Service Provider Entity Id
	 * @return
	 *   The Assertion Consumer Service Url.
	 */
	public function getServiceProviderAcs($entityId){
		return $this->trusted_sps[$entityId];
	}

	/**
	 * Returning a dummy IdP identifier.
	 *
	 * @return string
	 */
	public function getIdPId(){
		//return "https://tubers.coordinate.local";
		return 'https://'.$_SERVER['HTTP_HOST'];
	}

	/**
	 * Retrieves the certificate from the IdP.
	 *
	 * @return \LightSaml\Credential\X509Certificate
	 */
	public function getCertificate(){
		return \LightSaml\Credential\X509Certificate::fromFile('cert/coordinate.crt');
	}

	/**
	 * Retrieves the private key from the Idp.
	 *
	 * @return \RobRichards\XMLSecLibs\XMLSecurityKey
	 */
	public function getPrivateKey(){
		return \LightSaml\Credential\KeyHelper::createPrivateKey('cert/coordinate.key', '', true);
	}

}
