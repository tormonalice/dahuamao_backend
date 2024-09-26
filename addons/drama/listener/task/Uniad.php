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
 * 广告
 */
class Uniad
{
    // 观看广告成功后
    public function uniadSuccess($user_id){
        $user = Db::name('user')->find($user_id);
        // 增加剧场积分
        $task = Task::where('site_id', $user['site_id'])->where('hook', 'uniad_success')->where('status', 'normal')->find();
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
