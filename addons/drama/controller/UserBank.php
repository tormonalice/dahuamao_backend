<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

/**
 * 提现账户
 * Class UserBank
 * @package addons\drama\controller
 */
class UserBank extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];


    /**
     * 提现账户
     * @ApiParams   (name="type", type="string", required=true, description="提现账户类型：wechat微信，alipay支付宝，bank银行")
     */
    public function info()
    {
        $type = $this->request->get('type');
        try {
            $bankInfo = \addons\drama\model\UserBank::info($type);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('提现账户', $bankInfo);
    }

    /**
     * 编辑提现
     * @ApiMethod   (POST)
     * @ApiParams   (name="type", type="string", required=true, description="提现账户类型：wechat微信，alipay支付宝，bank银行")
     * @ApiParams   (name="real_name", type="string", required=true, description="真实姓名")
     * @ApiParams   (name="card_no", type="string", required=false, description="账号")
     * @ApiParams   (name="bank_name", type="string", required=false, description="开户行")
     * @ApiParams   (name="image", type="string", required=false, description="收款码")
     */
    public function edit()
    {
        $params = $this->request->post();
        if ($params['type'] === 'alipay') {
            $params['bank_name'] = '支付宝账户';
            if(!$params['card_no'] && !$params['image']){
                $this->error('支付宝提现账号和收款码必选添加一个！');
            }
        }elseif ($params['type'] === 'wechat') {
            $params['bank_name'] = '微信账户';
            $params['card_no'] = '';
            if(!$params['image']){
                $this->error('微信提现必须上传收款码！');
            }
        }else{
            if(!$params['card_no'] || !$params['bank_name']){
                $this->error('银行卡提现必须提交卡号和开户行！');
            }
        }
        $params['image'] = $params['image'] ?? '';

        // 表单验证
        $this->dramaValidate($params, get_class(), 'edit');

        $this->success('编辑成功', \addons\drama\model\UserBank::edit($params));
    }
}
