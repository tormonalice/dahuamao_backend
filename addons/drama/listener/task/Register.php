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
 * 用户注册
 */
class Register
{
    // 用户注册后
    public function userRegisterAfter($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'user_register_after')->where('status', 'normal')->find();
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

    // 绑定昵称后
    public function userBindNameAfter($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'user_bind_name_after')->where('status', 'normal')->find();
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

    // 绑定头像后
    public function userBindAvatarAfter($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'user_bind_avatar_after')->where('status', 'normal')->find();
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
