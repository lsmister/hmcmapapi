<?php

namespace App\Jobs;

use App\Libs\CtripStaticApi;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class GetMappingStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $ctripHotelCodes;

    protected $group;

    protected $service;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ctripHotelCodes, $group)
    {
        $this->ctripHotelCodes = $ctripHotelCodes;
        $this->group = $group;

        switch ($this->group) {
            case 'mrt':
                $this->service = new \App\Services\MrtMappingService();
                break;
            default:
                Log::error('GetMappingStatus group 不存在!');
                return;
                break;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::info("GetMappingStatus 开始执行...");

        $count = 0;
        $api = new CtripStaticApi();

        $codes = array_chunk($this->ctripHotelCodes, 5);
        foreach ($codes as $codearr) {
            $res = $api->mappingInfoSearch(true, $codearr);
            if ($res['code'] != 0) {
                Log::error("GetMappingStatus 获取数据错误, ", $res);
                break;
            }

            $count = $this->service->updHotelMatchStatus($res['datas']);
        }


        Log::info("GetMappingStatus 酒店更新统计: ".$count);
        Log::info("GetMappingStatus 执行完毕...");
        
        return;
    }


}
