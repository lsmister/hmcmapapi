<?php

namespace App\Libs;

use GuzzleHttp\Client;



define('GROUP_ID', 831);
define('USER_NAME', 'Huamin');
define('PASS_WORD', 'Huamin123456');


/**
 * 
 */
class CtripStaticApi
{

	public $client;

	protected $authorization;

	public $test_url = "https://gateway.fat.ctripqa.com/static/v2/json/";

	public $formal_url = "https://receive-vendor-hotel.ctrip.com/static/v2/json/";

	public $commonData;
	

	function __construct()
	{
		$this->client = new Client([
			'base_uri' => $this->formal_url
		]);

		$auth = hash("sha256", USER_NAME.':'.PASS_WORD);
		$this->authorization = $auth;
	}


	public function mappingInfoSearch($is_mapping, $hotel_ids = array(), $lang = 'zh-CN')
	{
		$data['languageCode'] = $lang;
		$data['getMappingInfoType'] = $is_mapping ? 'Mapping' : 'UnMapping';
		$data['hotelIds'] = $hotel_ids;

		dump($this->authorization);
		dd($data);


		$response = $this->client->request('POST', 'mappingInfoSearch', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		$res = json_decode($response, true);

		return $res;
	}
}