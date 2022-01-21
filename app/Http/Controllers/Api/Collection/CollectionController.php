<?php

namespace App\Http\Controllers\Api\Collection;

use App\Common\Constant;
use App\Http\Controllers\BaseController;
use App\Models\Collection\Collection;
use App\Models\User\User;
use App\Rules\Collection\CollectionIdsExists;
use App\Rules\Collection\EntityOfOtherIdInCollectionExists;
use App\Rules\Collection\OtherIdInCollectionExists;
use App\Services\Cache\TempAttributesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends BaseController
{
    //增加收藏
    public function addCollection(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'type' => [
                'required',
                'in:1,2,3'
            ],
            'other_id' => [
                'required',
                'unique:collections,other_id,0,id,type,' . $input['type'] . ',user_id,' . User::getCurrentUserCache('id'),
                new EntityOfOtherIdInCollectionExists($input['type'])
            ]
        ]);
        //只有公司才能收藏简历
        if ($input['type'] == Collection::RESUME) {
            if (User::getCurrentUserCache('type') != User::COMPANY_TYPE) {
                return $this->error(__('messages.noAuthority'), Constant::COMPANY_AUTHORITY_REQUIRED);
            }
        }
        //开启数据库事务
        DB::beginTransaction();
        try {
            //增加收藏
            Collection::create([
                'user_id' => User::getCurrentUserCache('id'),
                'type' => $input['type'],
                'other_id' => $input['other_id']
            ]);
            //更新用户的收藏数量
            User::updateCollectionCount(User::getCurrentUserCache('id'), $input['type']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除收藏
    public function deleteCollection(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $dataOperator = new TempAttributesService(['collectionInfo']);
        $request->validate([
            'type' => [
                'required',
                'in:1,2,3'
            ],
            'other_id' => [
                'required',
                new OtherIdInCollectionExists($input['type'], $dataOperator)
            ]
        ]);
        //获得收藏数据
        $collectionInfo = $dataOperator->getCollectionInfo();
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除收藏
            Collection::where('id', $collectionInfo['id'])->delete();
            //更新用户的收藏数量
            User::updateCollectionCount(User::getCurrentUserCache('id'), $input['type']);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }

    //删除多个收藏
    public function deleteCollections(Request $request)
    {
        //获得参数
        $input = $request->all();
        //验证参数
        $request->validate([
            'collection_ids' => [
                'required',
                'array',
                new CollectionIdsExists()
            ]
        ]);
        $ids = explode(',', $input['collection_ids']);
        //开启数据库事务
        DB::beginTransaction();
        try {
            //删除收藏
            Collection::whereIn('id', $ids)->delete();
            //更新用户的收藏数量
            User::updateCollectionCount(User::getCurrentUserCache('id'), Collection::COURSE);
            User::updateCollectionCount(User::getCurrentUserCache('id'), Collection::POSITION);
            User::updateCollectionCount(User::getCurrentUserCache('id'), Collection::RESUME);
            //提交数据库事务
            DB::commit();
        } catch (\Exception $e) {
            //数据库事务回滚
            DB::rollback();
            //返回数据库错误
            return $this->error(__('messages.DatabaseError'));
        }
        //返回成功
        return $this->success();
    }
}
