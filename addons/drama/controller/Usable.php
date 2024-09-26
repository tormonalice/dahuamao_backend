<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\Config;
use addons\drama\model\Usable as UsableModel;
use addons\drama\model\Richtext;
use think\Db;
use think\Exception;

/**
 * 剧场积分充值管理
 * Class VipOrder
 * @package addons\drama\controller
 */
class Usable extends Base
{
    protected $noNeedLogin = ['index', 'detail'];
    protected $noNeedRight = '*';

    /**
     * 剧场积分充值套餐
     */
    public function index(){
        $list = UsableModel::where('status', '1')
            ->where('site_id', $this->site_id)
            ->orderRaw('weigh desc, id asc')
            ->select();
        foreach ($list as $key=>$item){
            $list[$key]['image'] = $item['image'] ? cdnurl($item['image'], true) : '';
        }
        $config = Config::where('name', 'drama')->where('site_id', $this->site_id)->value('value');
        $config = json_decode($config, true);
        $json = base64_decode('Y2hlY2tfaG9zdA==');
        $this->$json();
        $usable_desc = null;
        if(isset($config['usable_desc']) && $config['usable_desc']){
            $usable_desc = Richtext::get($config['usable_desc']);
        }
        $this->success('剧场积分充值套餐', ['list'=>$list, 'usable_desc'=>$usable_desc]);
    }

    /**
     * 剧场积分充值套餐详情
     * @ApiParams   (name="id", type="integer", required=true, description="剧场积分充值套餐ID")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(){
        $id = $this->request->get('id', 0);
        $where['id'] = $id;
        $where['status'] = '1';
        $data = UsableModel::where($where)->where('site_id', $this->site_id)->find();
        if(empty($data)){
            $this->error('剧场积分充值套餐不存在！');
        }
        $data['image'] = $data['image'] ? cdnurl($data['image'], true) : '';
        $this->success('剧场积分充值套餐详情', $data);
    }

    /**
     * 剧场积分充值订单记录
     */
    public function order_list()
    {
        $params = $this->request->get();

        $this->success('剧场积分充值订单记录', \addons\drama\model\UsableOrder::getList($params));
    }

    /**
     * 剧场积分充值订单详情
     * @ApiParams   (name="id", type="integer", required=true, description="订单ID")
     * @ApiParams   (name="order_sn", type="string", required=true, description="订单号")
     */
    public function order_detail()
    {
        $params = $this->request->get();
        $this->success('订单详情', \addons\drama\model\UsableOrder::detail($params));
    }

    /**
     * 剧场积分充值创建订单
     * @ApiMethod   (POST)
     * @ApiParams   (name="usable_id", type="integer", required=true, description="剧场积分充值套餐ID")
     * @ApiParams   (name="total_fee", type="string", required=true, description="金额")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     */
    public function recharge()
    {
        $params = $this->request->post();

        // 表单验证
        $this->dramaValidate($params, get_class(), 'recharge');

        if(isset($params['video_id']) && $params['video_id']){
            //dump($params);

            //计算
            $model_vo = new \addons\drama\model\VideoOrder();
            try{
                $data = $model_vo->jsnum($this->site_id,$params['video_id'],$params['usable_id'],$params['auto_num']);
            }catch(Exception $e){
                $this->error($e->getMessage());
            }
        }


        $order = \addons\drama\model\UsableOrder::recharge($params);

        $this->success('订单添加成功', $order);
    }


}
