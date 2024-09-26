<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\User;
use addons\drama\model\UserWalletLog;
use think\Db;
use think\Exception;

/**
 * 任务
 * Class Task
 * @package addons\drama\controller
 */
class Task extends Base
{
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = ['*'];

    /**
     * 任务列表
     * @ApiParams   (name="platform", type="string", required=true, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $platform = $this->request->get('platform', 'wxOfficialAccount');
        $hooks = array_keys(\addons\drama\model\Task::$hooks);
        if($platform == 'H5' || $platform == 'Web' || $platform == 'wxOfficialAccount'){
            $hooks = ['user_register_after', 'user_bind_name_after', 'user_bind_avatar_after', 'share_success'];
        }
        if($platform == 'App'){
            $hooks = ['user_register_after', 'share_success'];
        }
        $task_list = Db::name('drama_task')
            ->where('site_id', $this->site_id)
            ->whereIn('hook', $hooks)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->field('id,title,desc,limit,usable,type,hook')
            ->select();
        $user = User::info();
        foreach ($task_list as &$task){
            $task['user_count'] = 0;
            if($user){
                $where['site_id'] = $this->site_id;
                $where['user_id'] = $user->id;
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
                $task['user_count'] = $count ?? 0;
            }
        }

        $this->success('剧场积分任务', $task_list);
    }

    /**
     * 广告任务详情
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function uniad(){
        $task = Db::name('drama_task')
            ->where('site_id', $this->site_id)
            ->whereIn('hook', 'uniad_success')
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->field('id,title,desc,limit,usable,type,hook')
            ->find();
        $user = User::info();
        $task['user_count'] = 0;
        if($user){
            $where['site_id'] = $this->site_id;
            $where['user_id'] = $user->id;
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
            $task['user_count'] = $count ?? 0;
        }

        $this->success('广告任务', $task);
    }

    /**
     * 任务成功
     * @ApiMethod   (POST)
     * @ApiParams   (name="type", type="string", required=true, description="share_wx_after分享微信，share_wxf_after分享朋友圈，uniad_success观看广告成功后")
     */
    public function add(){
        $this->repeat_filter();        // 防抖
        $type = $this->request->post('type');
        if(!in_array($type, ['share_wx_after', 'share_wxf_after', 'uniad_success'])){
            $this->error('参数错误');
        }
        $user_id = $this->auth->id;
        try {
            \think\Hook::listen($type, $user_id);
        }catch (Exception $e){
            $this->error('失败：'.$e->getMessage());
        }
        $this->success('成功');
    }
}