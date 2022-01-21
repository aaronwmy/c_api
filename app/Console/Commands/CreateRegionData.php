<?php

namespace App\Console\Commands;

use App\Models\Region\Region;
use Illuminate\Console\Command;

class CreateRegionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_region_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建地区数据';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (Region::count() > 0) exit('数据已存在，不能执行命令。');
        $path = app_path() . '/../region.txt';
        $myFile = fopen($path, "r") or die("文件打不开!");
        while (!feof($myFile)) {
            $line = fgets($myFile);
            $arr = preg_split('/[\s]+/', trim($line));
            $code = trim($arr[0]);
            $name = trim($arr[1]);
            if (substr($code, 2, 4) == '0000') {
                Region::create([
                    'region_name' => $name,
                    'region_code' => $code,
                    'level' => 1
                ]);
            } elseif (substr($code, 4, 2) == '00') {
                Region::create([
                    'region_name' => $name,
                    'region_code' => $code,
                    'level' => 2
                ]);
            } else {
                Region::create([
                    'region_name' => $name,
                    'region_code' => $code,
                    'level' => 3
                ]);
            }
        }
        fclose($myFile);
        $this->supplementSecondaryArea();
        $this->supplementProvinceMunicipality();
        $this->supplementIsCity();
        $this->supplementIsHot();
        $this->supplementFullName();
        $this->supplementPinyinAndInitials();
        return 0;
    }

    //补上直辖市的二级地区
    private function supplementSecondaryArea()
    {
        $list = Region::where('level', 1)->get()->toArray();
        for ($i = 0; $i < count($list); $i++) {
            if (Region::where('level', 2)->where([
                    ['region_code', '>', $list[$i]['region_code']],
                    ['region_code', '<', $list[$i]['region_code'] + 10000]
                ])->count() == 0) {
                Region::create([
                    'region_name' => $list[$i]['region_name'] . '城区',
                    'region_code' => $list[$i]['region_code'] + 100,
                    'level' => 2
                ]);
            }
        }
    }

    //补上省直辖市
    private function supplementProvinceMunicipality()
    {
        $list = Region::where('level', 3)->get();
        for ($i = 0; $i < count($list); $i++) {
            $info = Region::where('region_code', substr($list[$i]['region_code'], 0, 4) . '00')->first();
            if (empty($info)) {
                Region::create([
                    'region_code' => substr($list[$i]['region_code'], 0, 4) . '00',
                    'region_name' => '省直辖市',
                    'level' => 2
                ]);
            }
        }
    }

    //补上是否是城市
    private function supplementIsCity()
    {
        Region::where('level', 2)->where('region_name', '!=', '省直辖市')->update(['is_city' => 1]);
        $list = Region::where('level', 2)->where('region_name', '省直辖市')->get();
        for ($i = 0; $i < count($list); $i++) {
            Region::where(
                'region_code',
                '>',
                $list[$i]['region_code']
            )->where('region_code', '<', $list[$i]['region_code'] + 100)->update(['is_city' => 1]);
        }
    }

    //补上热门城市
    private function supplementIsHot()
    {
        Region::whereIn('region_name', [
            '北京市城区',
            '天津市城区',
            '上海市城区',
            '重庆市城区',
            '南京市',
            '杭州市',
            '武汉市',
            '广州市',
            '深圳市',
            '成都市'
        ])->update(['is_hot' => 1]);
    }

    //补上地区全称
    private function supplementFullName()
    {
        $list = Region::orderBy('region_code', 'asc')->get();
        $provinceName = null;
        $cityName = null;
        $areaName = null;
        for ($i = 0; $i < count($list); $i++) {
            if ($list[$i]['level'] == 1) {
                $provinceName = $list[$i]['region_name'];
                Region::where('region_code', $list[$i]['region_code'])->update(['region_fullname' => $provinceName]);
            } elseif ($list[$i]['level'] == 2) {
                $cityName = $provinceName . '-' . $list[$i]['region_name'];
                Region::where('region_code', $list[$i]['region_code'])->update(['region_fullname' => $cityName]);
            } elseif ($list[$i]['level'] == 3) {
                $areaName = $cityName . '-' . $list[$i]['region_name'];
                Region::where('region_code', $list[$i]['region_code'])->update(['region_fullname' => $areaName]);
            }
        }
    }

    //补上地区名称的拼音和拼音首字母
    private function supplementPinyinAndInitials()
    {
        $list = Region::orderBy('region_code', 'asc')->get();
        $pinyinConverter = app('pinyin');
        for ($i = 0; $i < count($list); $i++) {
            $pinyin = $pinyinConverter->sentence($list[$i]['region_name']);
            Region::where('region_code', $list[$i]['region_code'])->update([
                'pinyin' => str_replace(' ', '', $pinyin),
                'pinyin_initials' => substr($pinyin, 0, 1)
            ]);
        }
    }
}
