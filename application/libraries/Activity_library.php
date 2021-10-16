<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

use Aws\DynamoDb\Marshaler;
use Aws\DynamoDb\Exception\DynamoDbException;

class Activity_library
{
	private $CI;
	private $sdk;
	private $tableName;
	private $dynamodb;
	private $marshaler;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();

		// get config
		$this->CI->config->load('aws', TRUE);

		// Create an SDK class used to share configuration across clients.
		$this->sdk = $this->CI->aws_library->getSdk();

		$this->dynamodb = $this->sdk->createDynamoDb();
		$this->tableName = ENVIRONMENT . '_Activity_logs';
		$this->marshaler = new Marshaler();
	}

	public function createRecord($user, $action, $pageName, $url) {
		$info = json_encode([
			'action' => $action,
			'page_name' => $pageName,
			'user_name' => $user->first . ' ' . $user->surname,
			'url' => $url
		]);

		$item = $this->marshaler->marshalJson('
			{
				"user_id": ' . $user->staffID . ',
				"created_at": ' . time() . ',
				"info": ' . $info . '
			}
		');

		//it is impossible to save a record with same user_id + created_at keys
		$eav = $this->marshaler->marshalJson('
			{
				":userIdVal": '. $user->staffID .' ,
				":created_at": '. time() .' ,
				":info": '.$info.'
			}
		');

		$params = [
			'TableName' => $this->tableName,
			'Item' => $item,
			'ConditionExpression' => 'user_id <> :userIdVal AND created_at <> :created_at AND info <> :info',
			'ExpressionAttributeValues' => $eav
		];

		try {
			$result = $this->dynamodb->putItem($params);
			return $result;
		} catch (DynamoDbException $e) {
			return $e->getMessage();
		}
	}

	public function createTable() {

		$params = [
			'TableName' => $this->tableName,
			'KeySchema' => [
				[
					'AttributeName' => 'user_id',
					'KeyType' => 'HASH'
				],
				[
					'AttributeName' => 'created_at',
					'KeyType' => 'RANGE'
				]
			],
			'AttributeDefinitions' => [
				[
					'AttributeName' => 'user_id',
					'AttributeType' => 'N'
				],
				[
					'AttributeName' => 'created_at',
					'AttributeType' => 'N'
				],

			],
			'ProvisionedThroughput' => [
				'ReadCapacityUnits' => 10,
				'WriteCapacityUnits' => 10
			]
		];

		try {
			$result = $this->dynamodb->createTable($params);
			return $result;
		} catch (DynamoDbException $e) {
			echo "Unable to create table:\n";
			echo $e->getMessage() . "\n";
		}
	}

	public function checkExistsTable() {
		$response = $this->dynamodb->listTables();

		if (empty($response['TableNames'])) {
			return false;
		}

		if (!in_array($this->tableName, $response['TableNames'])) {
			return false;
		}

		return true;
	}

	public function getRecords($lastKey = 0, $searchData = []) {
		$eav = '{
			":user":' . $searchData['staff_id'];

		$params = [
			'TableName' => $this->tableName,
			'KeyConditionExpression' => 'user_id = :user',
			'ScanIndexForward' => false,
			'Limit' => 30
		];

		if (!empty($searchData)) {
			$eav .= ',":created_at_from":' . $searchData['date_from'];

			$params['KeyConditionExpression'] .= ' and created_at between :created_at_from and :created_at_to';

			if ($lastKey > 0) {
				$eav .= ',":created_at_to":' . $lastKey;
			} else {
				$eav .= ',":created_at_to":' . $searchData['date_to'];
			}
		}

		$eav .= '}';

		$eav = $this->marshaler->marshalJson($eav);

		$params['ExpressionAttributeValues'] = $eav;

		try {
			$result = $this->dynamodb->query($params);
			return $result;
		} catch (DynamoDbException $e) {
			return $e->getMessage();
		}
	}

	public function unmarshal($item) {
		return $this->marshaler->unmarshalItem($item);
	}
}
