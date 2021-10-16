<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config = array(
	'use_aws' => TRUE,
	'shared_config' => array(
		'region'  => AWS_REGION,
		'version' => 'latest'
	),
	's3' => array(
		'bucket' => AWS_S3_BUCKET
	),
	'cloudfront' => array(
		'domain' => '' // if entered, assets will be served over cloudfront if origin set on AWS
	)
);

/* End of file aws.php */
/* Location: ./application/config/aws.php */
