<?php


namespace App\Services;

use App\Models\Mrt\MrtHotel;
use App\Models\Mrt\MrtRoomType;
use App\Models\Mrt\MrtSubRoom;


class MrtMappingService
{

	public function updHotelMatchStatus($hotelLists)
	{
		$count = 0;

		foreach ($hotelLists as $list) {
			$flag = 1;
			if (empty($list['hotelCode'])) {
				$flag = 0;
			}

			MrtHotel::where('ctrip_hotel_code', $list['hotelId'])->update(['match_stauts' => $flag]);
			$count++;
		}

		return $count;
	}


	public function updRoomMatchStatus($hotelLists)
	{
		$count = 0;

		foreach ($hotelLists as $list) {
			if (count($list['subRoomLists']) == 0) {
				continue;
			}

			foreach ($list['subRoomLists'] as $roomlist) {
				# code...
			}
			
			$flag = 1;
			if (empty($list['hotelCode'])) {
				$flag = 0;
			}

			MrtHotel::where('ctrip_hotel_code', $list['hotelId'])->update(['match_stauts' => $flag]);
			$count++;
		}

		return $count;
	}


	public function updSubRoomMatchStatus($hotelLists)
	{
		$count = 0;

		foreach ($hotelLists as $list) {

			foreach ($list['subRoomLists'] as $roomlist) {
				if ($roomlist['code'] == 0) {
					Log::info($roomlist['message']);
				}else {
					Log::info($roomlist['sub_hotel_code']);
				}
			}
			
			$flag = 1;
			if (empty($list['hotelCode'])) {
				$flag = 0;
			}

			MrtHotel::where('ctrip_hotel_code', $list['hotelId'])->update(['match_stauts' => $flag]);
			$count++;
		}

		return $count;
	}
}