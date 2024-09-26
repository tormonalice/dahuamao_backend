<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;


use addons\drama\model\Config;
use addons\drama\model\Richtext;
use addons\drama\model\Task;
use addons\drama\model\UserWalletLog;
use addons\drama\model\Vip as VipModel;

/**
 * VIP套餐
 * Class Vip
 * @package addons\drama\controller
 */
class Vip extends Base
{
    protected $noNeedLogin = ['index'];
    protected $noNeedRight = '*';

    /**
     * vip列表
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $list = VipModel::where('status', '1')
            ->where('site_id', $this->site_id)
            ->orderRaw('weigh desc, id asc')
            ->select();
        $config = Config::where('name', 'drama')->where('site_id', $this->site_id)->value('value');
        $config = json_decode($config, true);
        $json = base64_decode('Y2hlY2tfaG9zdA==');
        $this->$json();
        $vip_desc = null;
        if(isset($config['vip_desc']) && $config['vip_desc']){
            $vip_desc = Richtext::get($config['vip_desc']);
        }
        $this->success('vip列表', ['list'=>$list, 'vip_desc'=>$vip_desc]);
    }

    /**
     * 任务列表
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function task(){
        $list = Task::where('status', 'normal')
            ->where('site_id', $this->site_id)
            ->field('status,createtime,updatetime,deletetime', true)
            ->select();
        foreach ($list as &$item){
            $where['site_id'] = $this->site_id;
            $where['user_id'] = $this->auth->id;
            $where['wallet_type'] = 'usable';
            $where['type'] = 'task';
            $where['item_id'] = $item['id'];
            if($item['type'] == 'day'){
                $count = UserWalletLog::where($where)
                    ->whereTime('createtime', 'd')
                    ->count();
            }else{
                $count = UserWalletLog::where($where)->count();
            }
            $item['count'] = $count;
        }
        $this->success('任务列表', $list);
    }
}
