<?php

namespace App\Console\Commands;

use App\Models\Recruitment\RecruitmentPositionType;
use App\Models\Region\Region;
use Illuminate\Console\Command;

class CreatePositionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create_position_data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建职位数据';

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
        if (RecruitmentPositionType::count() > 0) exit('数据已存在，不能执行命令。');
        $path = app_path() . '/../position.txt';
        $data = json_decode(file_get_contents($path), true);
        for ($i = 0; $i < count($data); $i++) {
            $firstInfo = RecruitmentPositionType::create(['fid' => 0, 'level' => 1, 'type_name' => $data[$i]['name']]);
            for ($j = 0; $j < count($data[$i]['subLevelModelList']); $j++) {
                $secondInfo = RecruitmentPositionType::create(['fid' => $firstInfo['id'], 'level' => 2, 'type_name' => $data[$i]['subLevelModelList'][$j]['name']]);
                for ($k = 0; $k < count($data[$i]['subLevelModelList'][$j]['subLevelModelList']); $k++) {
                    RecruitmentPositionType::create(['fid' => $secondInfo['id'], 'level' => 3, 'type_name' => $data[$i]['subLevelModelList'][$j]['subLevelModelList'][$k]['name']]);
                }
            }
        }
        return 0;
    }
}
