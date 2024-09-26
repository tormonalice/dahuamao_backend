<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\listener\task;

use addons\drama\model\Task;
use addons\drama\model\User as UserModel;
use addons\drama\model\UserWalletLog;
use think\Db;

/**
 * 分享
 */
class Share
{
    // 分享微信后
    public function shareWxAfter($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'share_wx_after')->where('status', 'normal')->find();
        if($task){
            $where['site_id'] = $user['site_id'];
            $where['user_id'] = $user_id;
            $where['wallet_type'] = 'usable';
            $where['type'] = 'task';
            $where['item_id'] = $task['id'];
            if($task['type'] == 'day'){
                $count = UserWalletLog::where($where)
                    ->whereTime('createtime', 'd')
                    ->count();
            }else{
                $count = UserWalletLog::where($where)->count();
            }
            if($count < $task['limit']){
                UserModel::usable($task['usable'], $user_id, 'task', $task['id'], $task['title']);
            }
        }
    }

    // 分享微信朋友圈后
    public function shareWxfAfter($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'share_wxf_after')->where('status', 'normal')->find();
        if($task){
            $where['site_id'] = $user['site_id'];
            $where['user_id'] = $user_id;
            $where['wallet_type'] = 'usable';
            $where['type'] = 'task';
            $where['item_id'] = $task['id'];
            if($task['type'] == 'day'){
                $count = UserWalletLog::where($where)
                    ->whereTime('createtime', 'd')
                    ->count();
            }else{
                $count = UserWalletLog::where($where)->count();
            }
            if($count < $task['limit']){
                UserModel::usable($task['usable'], $user_id, 'task', $task['id'], $task['title']);
            }
        }
    }

    // 分享成功后（用户注册）
    public function shareSuccess($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'share_success')->where('status', 'normal')->find();
        if($task){
            $where['site_id'] = $user['site_id'];
            $where['user_id'] = $user_id;
            $where['wallet_type'] = 'usable';
            $where['type'] = 'task';
            $where['item_id'] = $task['id'];
            if($task['type'] == 'day'){
                $count = UserWalletLog::where($where)
                    ->whereTime('createtime', 'd')
                    ->count();
            }else{
                $count = UserWalletLog::where($where)->count();
            }
            if($count < $task['limit']){
                UserModel::usable($task['usable'], $user_id, 'task', $task['id'], $task['title']);
            }
        }
    }

}
