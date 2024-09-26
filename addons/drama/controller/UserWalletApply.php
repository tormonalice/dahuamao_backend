<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;


/**
 * 提现管理
 * Class UserWalletApply
 * @package addons\drama\controller
 */
class UserWalletApply extends Base
{
    protected $noNeedLogin = ['rule'];
    protected $noNeedRight = ['*'];

    /**
     * 提现记录
     */
    public function index()
    {
        $this->success('提现记录', \addons\drama\model\UserWalletApply::getList());
    }


    /**
     * 申请提现
     * @ApiMethod   (POST)
     * @ApiParams   (name="type", type="string", required=true, description="提现类型：wechat微信，alipay支付宝，bank银行")
     * @ApiParams   (name="money", type="string", required=true, description="提现金额")
     * @ApiParams   (name="platform", type="string", required=true, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     * @throws \addons\drama\exception\Exception
     */
    public function apply()
    {
        $this->repeat_filter();        // 防抖
        $type = $this->request->post('type');
        $money = $this->request->post('money');
        $platform = $this->request->post('platform', '');
        $apply = \think\Db::transaction(function () use ($type, $money, $platform) {
            try {
                return \addons\drama\model\UserWalletApply::apply($type, $money, $platform);
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
        });
        if($apply) {
            $this->success('申请成功');            
        }
        $this->error('申请失败');
    }

    /**
     * 提现规则
     */
    public function rule()
    {
        $config = \addons\drama\model\UserWalletApply::getWithdrawConfig();
        $min = round(floatval($config['min']), 2);
        $max = round(floatval($config['max']), 2);
        $service_fee = floatval($config['service_fee']) * 100;
        $service_fee = round($service_fee, 1);      // 1 位小数
        $perday_amount = isset($config['perday_amount']) ? round(floatval($config['perday_amount']), 2) : 0;
        $perday_num = isset($config['perday_num']) ? round(floatval($config['perday_num']), 2) : 0;

        $rule = [
            'min' => $min,
            'max' => $max,
            'service_fee' => $service_fee,
            'perday_amount' => $perday_amount,
            'perday_num' => $perday_num,
            'methods' => $config['methods'] ?? []
        ];

        $this->success('提现规则', $rule);
    }
}
