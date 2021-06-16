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
			'base_uri' => $this->test_url
		]);

		$auth = hash("sha256", USER_NAME.':'.PASS_WORD);
		$this->authorization = $auth;
	}


	/**
	 * 酒店静态信息推送
	 */
	public function hotelStaticPush($hotelInfo, $active = true, $lang = 'en-US')
	{

		$data['languageCode'] = $lang;
		$data['hotelCode'] = $hotelInfo['code'];
		$data['active'] = $active;

		$hotelNames[0] = [
			'languageCode' => 'en-US',
			'content' => $hotelInfo['en_name']
		];
		if (!empty($hotelInfo['cn_name'])) {
			$hotelNames[1] = [
				'languageCode' => 'zh-CN',
				'content' => $hotelInfo['cn_name']
			];
		}
		
		$data['hotelBasicInfo'] = [
			'hotelNames' => $hotelNames,
			'currency' => $hotelInfo['currency'],
			'positions' => [
				[
					"source" => "google",
	                "latitude" => $hotelInfo['latitude'],
	                "longitude" => $hotelInfo['longitude']
				]
			],
			'addresses' => [
				[
					'languageCode' => 'en-US',
					'country' => $hotelInfo['country_name'],
					'province' => $hotelInfo['province_name'],
					'address' => $hotelInfo['address']
				]
			],
			'addressVisible' => true,
			'phones' => [
				[
					'phoneType' => $hotelInfo['phone_type'],
					'phoneNumber' => [
						'countryCode' => $hotelInfo['phone_country_code'],
						'areaCode' => $hotelInfo['phone_area_code'],
						'mainCode' => $hotelInfo['phone_main_code']
					]
				]
			]
		];

		$response = $this->client->request('POST', 'hotelNotify', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		return $response;

		$res = json_decode($response, true);

		return $res;
	}


	/**
	 * 基础房型静态信息推送
	 */
	public function roomStaticPush($hotelCode, $roomInfos, $active = true, $lang = 'en-US')
	{

		$data['languageCode'] = $lang;
		$data['hotelCode'] = $hotelCode;

		foreach ($roomInfos as $k => $roomInfo) {
			if (empty($roomInfo['en_name']) && empty($roomInfo['cn_name'])) {
				$roomNames[0] = [
					'languageCode' => 'en-US',
					'content' => $roomInfo['room_name']
				];
			}else {
				if (!empty($roomInfo['en_name'])) {
					$roomNames[0] = [
						'languageCode' => 'en-US',
						'content' => $roomInfo['en_name']
					];
				}

				if (!empty($roomInfo['cn_name'])) {
					$roomNames[0] = [
						'languageCode' => 'zh-CN',
						'content' => $roomInfo['cn_name']
					];
				}
			}

			$roomDescriptions[0] = [
				'languageCode' => 'en-US',
                'content' => $roomInfo['en_describe']
			];
			if (!empty($roomInfo['cn_describe'])) {
				$roomDescriptions[1] = [
					'languageCode' => 'zh-CN',
                	'content' => $roomInfo['cn_describe']
				];
			}
			
			$data['roomDatas'][$k] = [
				'roomTypeCode' => $roomInfo['room_type_code'],
				'pmsRoomTypeCode' => $roomInfo['pms_room_type_code'],
				'active' => $active,
				'roomBasicInfo' => [
					'currency' => $roomInfo['currency'],
					'roomNames' => $roomNames,
					'roomDescriptions' => $roomDescriptions,
					'occupancy' => [
						'maxOccupancy' => $roomInfo['max_occupancy'],
						'adult' => [
                        	'maxAdultOccupancy' => $roomInfo['max_occupancy']
                    	],
                    	'children' => [
                    		'isAllowChildren' => (bool)$roomInfo['is_allow_children'],
                    		'sharingBedChildrenOccupancy' => $roomInfo['sbc_occupancy']
                    	]
					],
					'smoking' => $roomInfo['has_smoke'],
					'window' => $roomInfo['has_window'],
					'area' => $roomInfo['size'],
					'wifi' => [
						'available' => $roomInfo['has_wifi']
					],
					'cableInternet' => [
						'available' => $roomInfo['has_cableinter']
					],
				]
			];
		}
		

		$response = $this->client->request('POST', 'roomNotify', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		return $response;

		$res = json_decode($response, true);

		return $res;
	}


	/**
	 * 子房型静态信息推送
	 */
	public function subRoomStaticPush($hotelCode, $subRoomInfos, $active = true, $lang = 'en-US')
	{

		$data['languageCode'] = $lang;
		$data['hotelCode'] = $hotelCode;

		foreach ($subRoomInfos as $k => $subRoomInfo) {
			$data['roomTypes'][$k] = [
				[
					'roomTypeCode' => $subRoomInfo['room_type_code'],
				]
			];
		}
		

		
		

		$response = $this->client->request('POST', 'roomNotify', [
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


	/**
	 * 酒店信息查询
	 */
	public function hotelInfoSearch($ctripSubHotelIds, $lang = 'en-US')
	{

		$data['languageCode'] = $lang;
		$data['hotelIds'] = $ctripSubHotelIds;

		// dd(json_encode($data));

		$response = $this->client->request('POST', 'hotelInfoSearch', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		return $response;

		$res = json_decode($response, true);

		return $res;
	}


	/**
	 * 母基信息查询
	 */
	public function masterRoomInfoSearch($ctripSubHotelIds, $lang = 'en-US')
	{

		$data['languageCode'] = $lang;
		$data['hotelIds'] = $ctripSubHotelIds;
		

		$response = $this->client->request('POST', 'masterRoomInfoSearch', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		return $response;

		$res = json_decode($response, true);

		return $res;
	}


	/**
	 * 房型MAPPING信息
	 */
	public function mappingInfoSearch($is_mapping, $ctripSubHotelIds = array(), $lang = 'zh-CN')
	{
		$data['languageCode'] = $lang;
		$data['getMappingInfoType'] = $is_mapping ? 'Mapping' : 'UnMapping';
		$data['hotelIds'] = $ctripSubHotelIds;

		$response = $this->client->request('POST', 'mappingInfoSearch', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		return $response;

		$res = json_decode($response, true);

		return $res;
	}


	/**
	 * 写入酒店/房型MAPPING关系
	 * @set_type 写入类型 addMapping,updateMapping,deleteHotelMapping,deleteRoomMapping
	 */
	public function mappingInfoSet($set_type, $ctripSubHotelId, $hotel_code, $lang = 'zh-CN', $roomlists)
	{
		$data['languageCode'] = $lang;
		$data['SetType'] = $set_type;
		$data['hotelId'] = $ctripSubHotelId;
		$data['hotelCode'] = $hotel_code;

		foreach ($roomlists as $key => $room) {
			$data['subRoomMappings'][0] = [
				'subRoomId' => $room['ctrip_subroom_id'],
				'roomTypeCode' => $room['room_type_code'],
				'ratePlanCode' => $room['rate_plan_code']
			];
		}
		
		$response = $this->client->request('POST', 'mappingInfoSet', [
			'headers' => [
		        'Code' => GROUP_ID,
		        'Authorization' => $this->authorization,
		        'Accept' => 'application/json'
		    ],
		    'json' => $data,
		    'verify' => false
		])->getBody()->getContents();

		dd($response);

		$res = json_decode($response, true);

		return $res;
	}
}