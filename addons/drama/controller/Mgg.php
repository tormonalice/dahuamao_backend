<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\Config;
use app\admin\model\drama\Mgg as MggModel;
use addons\drama\model\Richtext;
use app\admin\model\drama\MggOrder;
use think\Db;

/**
 * 分销管理
 * Class VipOrder
 * @package addons\drama\controller
 */
class Mgg extends Base
{
    protected $noNeedLogin = ['index', 'detail','mggswitch'];
    protected $noNeedRight = '*';

    /**
     * 免广告等级
     */
    public function index(){
        $list = MggModel::where('status', 'normal')
            ->where('site_id', $this->site_id)
            ->orderRaw('weigh desc, id asc')
            ->select();
        foreach ($list as $key=>$item){
            $list[$key]['image'] = $item['image'] ? cdnurl($item['image'], true) : '';
            $list[$key]['expire_text'] = $item['expire'] == 0 ? '永久' : intval($item['expire']/86400).'天';
        }
        $config = Config::where('name', 'drama')->where('site_id', $this->site_id)->value('value');
        $config = json_decode($config, true);
        $mgg_desc = null;
        if(isset($config['mgg_desc']) && $config['mgg_desc']){
            $mgg_desc = Richtext::get($config['mgg_desc']);
        }
        $this->success('免广告等级', ['list'=>$list, 'mgg_desc'=>$mgg_desc]);
    }

    /**
     * 分销等级详情
     * @ApiParams   (name="id", type="integer", required=true, description="分销等级ID")
     * @ApiParams   (name="level", type="integer", required=true, description="分销等级")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(){
        $id = $this->request->get('id', 0);
        $level = $this->request->get('level', 0);
        if($id){
            $where['id'] = $id;
        }
        if($level){
            $where['level'] = $level;
        }
        $where['status'] = 'normal';
        $data = MggModel::where($where)->where('site_id', $this->site_id)->find();
        if(empty($data)){
            $this->error('等级不存在！');
        }
        $data['image'] = $data['image'] ? cdnurl($data['image'], true) : '';
        $data['expire_text'] = $data['expire'] == 0 ? '永久' : intval($data['expire']/86400).'天';
        $this->success('详情', $data);
    }

    //免广告开关
    public function mggswitch(){
        $config = Config::where(['site_id'=>$this->site_id,'name'=>'mgg'])->value('value');
        if($config) {
            $config = json_decode($config, true);
            if (isset($config['mgg_switch']) && $config['mgg_switch'] == 1) {
                $this->success('ok', ['switch' => 1]);
            }
        }

        $this->success('ok',['switch'=>0]);

    }

    /**
     * 免广告订单记录
     */
    public function order_list()
    {
        $params = $this->request->get();

        $this->success('免广告订单记录', MggOrder::getList($params));
    }

    /**
     * 免广告订单详情
     * @ApiParams   (name="id", type="integer", required=true, description="订单ID")
     * @ApiParams   (name="order_sn", type="string", required=true, description="订单号")
     */
    public function order_detail()
    {
        $params = $this->request->get();
        $this->success('订单详情', MggOrder::detail($params));
    }

    /**
     * 免广告创建订单
     * @ApiMethod   (POST)
     * @ApiParams   (name="mgg_id", type="integer", required=true, description="免广告ID")
     * @ApiParams   (name="total_fee", type="string", required=true, description="金额")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     */
    public function recharge()
    {
        $params = $this->request->post();

        // 表单验证
        $this->dramaValidate($params, get_class(), 'recharge');

        $order = MggOrder::recharge($params);

        $this->success('订单添加成功', $order);
    }


}
