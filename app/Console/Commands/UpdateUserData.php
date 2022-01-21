<?php

namespace App\Console\Commands;

use App\Models\Collection\Collection;
use App\Models\SiteMail\SiteMail;
use App\Models\User\User;
use Illuminate\Console\Command;

class UpdateUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update_user_data {user_ids?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新用户数据';

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
        $userIds = $this->argument('user_ids');
        $User = new User();
        if (!empty($userIds)) {
            $userIdsArr = explode(',', $userIds);
            $User = $User->whereIn('id', $userIdsArr);
        }
        $userList = $User->get();
        foreach ($userList as $item) {
            //更新用户的收藏数量
            User::updateCollectionCount($item['id'], Collection::COURSE);
            User::updateCollectionCount($item['id'], Collection::POSITION);
            User::updateCollectionCount($item['id'], Collection::RESUME);
            //更新用户的关注数量
            User::updateUserFollowCount($item['id']);
            //更新用户的粉丝数量
            User::updateFollowUserCount($item['id']);
            //更新用户的已审核的课程数量
            User::updateApprovedCourseCount($item['id']);
            //更新用户的未读的站内信数量
            User::where('id', $item['id'])->update(['count_of_unread_site_mail' => SiteMail::getSystemMessageCount($item['id'], SiteMail::IS_NOT_READ)]);
        }
        return 0;
    }
}
