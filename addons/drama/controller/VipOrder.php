<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;


use addons\drama\model\Vip;

/**
 * Vip订单
 * Class VipOrder
 * @package addons\drama\controller
 */
class VipOrder extends Base
{
    protected $noNeedLogin = [];
    protected $noNeedRight = '*';

    /**
     * VIP购买记录
     */
    public function index()
    {
        $params = $this->request->get();

        $this->success('VIP购买记录', \addons\drama\model\VipOrder::getList($params));
    }

    /**
     * 订单详情
     * @ApiParams   (name="id", type="integer", required=true, description="订单ID")
     * @ApiParams   (name="order_sn", type="string", required=true, description="订单号")
     */
    public function detail()
    {
        $params = $this->request->get();
        $this->success('订单详情', \addons\drama\model\VipOrder::detail($params));
    }


    /**
     * 创建订单
     * @ApiMethod   (POST)
     * @ApiParams   (name="vip_id", type="integer", required=true, description="vip id")
     * @ApiParams   (name="total_fee", type="string", required=true, description="金额")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     */
    public function recharge()
    {
        $params = $this->request->post();

        // 表单验证
        $this->dramaValidate($params, get_class(), 'recharge');

        $order = \addons\drama\model\VipOrder::recharge($params);

        $this->success('订单添加成功', $order);
    }


}
