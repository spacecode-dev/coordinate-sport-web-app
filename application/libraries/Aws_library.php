<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Aws_library {

	private $CI;
	private $sdk;
	private $s3_client = FALSE;
	private $s3_config;

	public function __construct() {
//        putenv("HOME=/var/www/html");
		// get CI instance
		$this->CI =& get_instance();

		// get config
		$this->CI->config->load('aws', TRUE);

		// Create an SDK class used to share configuration across clients.
		$this->sdk = new Aws\Sdk($this->CI->config->item('shared_config', 'aws'));
	}

	/**
	 * init s3 client if doesn't exist
	 * @return string bucket name
	 */
	public function init_s3() {
		// if doesn't exist, create
		if ($this->s3_client === FALSE) {
			$this->s3_client = $this->sdk->createS3();

			// get config
			$this->s3_config = $this->CI->config->item('s3', 'aws');

			// register stream wrapper
			$this->s3_client->registerStreamWrapper();
		}

		return $this->s3_config['bucket'];
	}

	/**
	 * generate presigned url
	 * @param  string $file
	 * @param  string $expiration
	 * @return string presigned url
	 */
	public function s3_presigned_url($file, $expiration = '+10 minutes') {

		$bucket =  $this->s3_config['bucket'];

		if (isset($this->CI->auth->user->accountID)) {
			$file = $this->CI->auth->user->accountID . '/' . $file;
		}

		// get object
		$cmd = $this->s3_client->getCommand('GetObject', [
			'Bucket' => $bucket,
			'Key'=> $file
		]);

		// request url
		$request = $this->s3_client->createPresignedRequest($cmd, $expiration);

		// Get the actual presigned-url
		return (string) $request->getUri();
	}

	public function getSdk() {
		return $this->sdk;
	}

}