<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\exception\Exception;
use addons\drama\model\Config;
use addons\drama\model\ResellerOrder;
use addons\drama\model\UsableOrder;
use addons\drama\model\User;
use addons\drama\model\VipOrder;
use app\admin\model\drama\MggOrder;
use think\Cache;
use think\Db;
use think\Log;
use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Pay as YansongdaPay;
use addons\drama\library\Wechat as Wc;

/**
 * 支付
 * Class Pay
 * @package addons\drama\controller
 */
class Pay extends Base
{

    protected $noNeedLogin = ['notifyx', 'notifyr', 'confirm'];
    protected $noNeedRight = ['*'];


    /**
     * 支付宝网页支付
     * @ApiInternal
     */
    public function alipay()
    {
        $order_sn = $this->request->get('order_sn');
        $platform = $this->request->get('platform');

        list($order, $prepay_type) = $this->getOrderInstance($order_sn);
        $order = $order->where('order_sn', $order_sn)->where('site_id', $this->site_id)->find();

        try {
            if (!$order) {
                throw new \Exception("订单不存在");
            }
            if ($order->status > 0) {
                throw new \Exception("订单已支付");
            }
            if ($order->status < 0) {
                throw new \Exception("订单已失效");
            }

            $order_data = [
                'order_id' => $order->id,
                'out_trade_no' => $order->order_sn,
                'total_fee' => $order->total_fee,
                'subject' => '商城订单支付',
            ];

            $notify_url = $this->request->root(true) . '/addons/drama/pay/notifyx/payment/alipay/platform/H5/sign/'.$this->sign;
            $pay = new \addons\drama\library\PayService($this->site_id, 'alipay', 'url', $notify_url);
            $result = $pay->create($order_data);

            $result = $result->getContent();

            echo $result;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        // $this->assign('result', $result);

        // return $this->view->fetch();
    }


    /**
     * 拉起支付
     * @ApiMethod   (POST)
     * @ApiParams   (name="order_sn", type="string", required=true, description="订单号")
     * @ApiParams   (name="payment", type="string", required=true, description="支付平台默认wechat")
     * @ApiParams   (name="openid", type="string", required=true, description="微信openid")
     * @ApiParams   (name="platform", type="string", required=true, description="平台:wxMiniProgram=微信小程序")
     */
    public function prepay()
    {
        if (!class_exists(\Yansongda\Pay\Pay::class)) {
            new \addons\drama\exception\Exception('请先配置支付插件');
        }

        $user = User::info();
        $order_sn = $this->request->post('order_sn');
        $payment = $this->request->post('payment', 'wallet');
        $openid = $this->request->post('openid', '');
        $platform = $this->request->post('platform');

        list($order, $prepay_type) = $this->getOrderInstance($order_sn);
        $order = $order->where('user_id', $user->id)->where('order_sn', $order_sn)->where('site_id', $this->site_id)->find();

        if (!$order) {
            $this->error("订单不存在");
        }

        if (in_array($order->status, [$order::STATUS_INVALID, $order::STATUS_CANCEL])) {
            $this->error("订单已失效");
        }

        if (!$payment || !in_array($payment, ['wechat', 'alipay', 'wallet'])) {
            $this->error("支付类型不能为空");
        }

        if ($payment == 'wallet' && $prepay_type == 'order') {
            // 余额支付
            $this->walletPay($order, $payment, $platform);
        }

        $order_data = [
            'order_id' => $order->id,
            'out_trade_no' => $order->order_sn,
            'total_fee' => $order->total_fee,
        ];

        // 微信公众号，小程序支付，必须有 openid
        if ($payment == 'wechat') {
            if (in_array($platform, ['wxOfficialAccount', 'wxMiniProgram'])) {
                if (isset($openid) && $openid) {
                    // 如果传的有 openid
                    $order_data['openid'] = $openid;
                } else {
                    // 没有 openid 默认拿下单人的 openid
                    $oauth = \addons\drama\model\UserOauth::where([
                        'user_id' => $order->user_id,
                        'provider' => 'Wechat',
                        'platform' => $platform
                    ])->find();

                    $order_data['openid'] = $oauth ? $oauth->openid : '';
                }

                if (empty($order_data['openid'])) {
                    // 缺少 openid
                    return $this->success('缺少 openid', 'no_openid');
                }
            }

            $order_data['body'] = '订单支付';
        } else {
            $order_data['subject'] = '订单支付';
        }

        try {
            $notify_url = $this->request->root(true) . '/addons/drama/pay/notifyx/payment/' . $payment . '/platform/' . $platform . '/sign/' . $this->sign;
            $pay = new \addons\drama\library\PayService($this->site_id, $payment, $platform, $notify_url);
            $result = $pay->create($order_data);
        } catch (\Exception $e) {
            $this->error("支付配置错误：" . $e->getMessage());
        }

        if ($platform == 'Web') {
            $result = $result->code_url;
        }
        if ($platform == 'H5' && $payment == 'wechat') {
            $result = $result->getContent();
        }

        return $this->success('获取预付款成功', [
            'pay_data' => $result,
        ]);
    }

    /**
     * 虚拟支付开关
     */
    public function xunipayswitch(){
        $xunipay_config = Config::where(['site_id'=>$this->site_id,'name'=>'xunipay'])->find();
        if($xunipay_config){
            $xunipay_config = json_decode($xunipay_config['value'], true);
            if($xunipay_config['pay_switch']){
                $this->success('ok',['xunipay_switch'=>1]);
            }else{
                $this->success('ok',['xunipay_switch'=>0]);
            }
        }else{
            $this->success('ok',['xunipay_switch'=>0]);
        }
    }

    /**
     * 拉起虚拟支付
     * @ApiMethod   (POST)
     * @ApiParams   (name="order_sn", type="string", required=true, description="订单号")
     * @ApiParams   (name="payment", type="string", required=true, description="支付平台默认wechat")
     * @ApiParams   (name="openid", type="string", required=true, description="微信openid")
     * @ApiParams   (name="platform", type="string", required=true, description="平台:wxMiniProgram=微信小程序")
     */
    public function xunipay()
    {
        if (!class_exists(\Yansongda\Pay\Pay::class)) {
            new \addons\drama\exception\Exception('请先配置支付插件');
        }

        $user = User::info();
        $order_sn = $this->request->post('order_sn');
        $payment = $this->request->post('payment', 'wallet');
        $openid = $this->request->post('openid', '');
        $platform = $this->request->post('platform');

        list($order, $prepay_type) = $this->getOrderInstance($order_sn);
        $order = $order->where('user_id', $user->id)->where('order_sn', $order_sn)->where('site_id', $this->site_id)->find();

        if (!$order) {
            $this->error("订单不存在");
        }

        if (in_array($order->status, [$order::STATUS_INVALID, $order::STATUS_CANCEL])) {
            $this->error("订单已失效");
        }

        if (!$payment || !in_array($payment, ['wechat'])) {
            $this->error("支付类型不能为空");
        }

        /*
        if ($payment == 'wallet' && $prepay_type == 'order') {
            // 余额支付
            $this->walletPay($order, $payment, $platform);
        }
        */

        $order_data = [
            'order_id' => $order->id,
            'out_trade_no' => $order->order_sn,
            'total_fee' => $order->total_fee,
        ];

        // 微信公众号，小程序支付，必须有 openid
        if ($payment == 'wechat') {
            if (in_array($platform, ['wxOfficialAccount', 'wxMiniProgram'])) {
                if (isset($openid) && $openid) {
                    // 如果传的有 openid
                    $order_data['openid'] = $openid;
                } else {
                    // 没有 openid 默认拿下单人的 openid
                    $oauth = \addons\drama\model\UserOauth::where([
                        'user_id' => $order->user_id,
                        'provider' => 'Wechat',
                        'platform' => $platform
                    ])->find();

                    $order_data['openid'] = $oauth ? $oauth->openid : '';
                }

                if (empty($order_data['openid'])) {
                    // 缺少 openid
                    return $this->success('缺少 openid', 'no_openid');
                }
            }

            $order_data['body'] = '订单支付';
        } else {
            $order_data['subject'] = '订单支付';
        }

        $code = $this->request->param('code',false);
        if(!$code){
            $this->error('缺少参数code');
        }

        try {
            $xunipay_config = Config::where(['site_id'=>$this->site_id,'name'=>'xunipay'])->find();
            if($xunipay_config){
                $xunipay_config = json_decode($xunipay_config['value'], true);
                if($xunipay_config['pay_switch']){
                    if(!$xunipay_config['app_id'] || !$xunipay_config['offer_id']){
                        $this->error('请先配置微信小程序虚拟支付参数');
                    }
                }else{
                    $this->error('请先在后台打开微信小程序虚拟支付开关');
                }
            }else{
                $this->error('请先配置微信小程序虚拟支付参数');
            }
        }catch (HttpException $e){
            $this->error('微信小程序虚拟支付配置错误：'.$e->getMessage());
        }

        try {
            $wechat = new Wc('wxMiniProgram');
            $json = $wechat->getApp()->auth->session($code);
        }catch (HttpException $e){
            $this->error('小程序配置错误：'.$e->getMessage());
        }

        if (!isset($json['openid'])) {
            $this->error("登录失败:session_key，".$json['errmsg']);
        }

        try {
            $notify_url = $this->request->root(true) . '/addons/drama/pay/notifyx/payment/' . $payment . '/platform/' . $platform . '/sign/' . $this->sign;

            if($platform == 'wxMiniProgram'){
                $result['signData'] = [
                    'offerId' => $xunipay_config['offer_id'],
                    'buyQuantity' => 1,
                    'env' => 0,
                    'currencyType' => 'CNY',
                    'platform' => 'android',
                    'productId' => $order->product_id,
                    'goodsPrice' => (int)bcmul($order->total_fee,100,0),
                    'outTradeNo' => $order->order_sn,
                    'attach' => $order->order_sn,
                ];

                $result['mode'] = 'short_series_goods';

                $result['paySig'] = hash_hmac('sha256','requestVirtualPayment&'.json_encode($result['signData']), $xunipay_config['app_key']);

                $result['signature'] = hash_hmac('sha256',json_encode($result['signData']), $json['session_key']);

            }else{
                $pay = new \addons\drama\library\PayService($this->site_id, $payment, $platform, $notify_url);
                $result = $pay->create($order_data);
            }

        } catch (\Exception $e) {
            $this->error("支付配置错误：" . $e->getMessage());
        }

        /*
        if ($platform == 'Web') {
            $result = $result->code_url;
        }
        if ($platform == 'H5' && $payment == 'wechat') {
            $result = $result->getContent();
        }
        */

        return $this->success('获取预付款成功', [
            'pay_data' => $result,
        ]);
    }

    /**
     * 查询支付结果
     * @ApiMethod   (POST)
     * @ApiParams   (name="payment", type="string", required=true, description="支付平台默认wechat")
     * @ApiParams   (name="orderid", type="string", required=true, description="订单编号")
     */
    public function checkPay(){
        $payment = $this->request->post('payment', 'wechat');
        $orderid = $this->request->post("orderid", '');
        if (!$payment || !in_array($payment, ['wechat', 'alipay'])) {
            $this->error("支付类型不能为空");
        }
        //发起PC支付(Scan支付)(PC扫码模式)
        $pay = new \addons\drama\library\PayService($this->site_id, $payment, 'Web');
        try {
            $result = $pay->checkPay($orderid, 'scan');
            if ($result) {
                $this->success("", $result);
            } else {
                $this->error("查询失败");
            }
        } catch (GatewayException $e) {
            $this->error("查询失败");
        }
    }

    /**
     * 余额支付
     * @ApiInternal
     * @param $order
     * @param $type
     * @param $method
     */
    public function walletPay ($order, $type, $method) {
        // $order = Db::transaction(function () use ($order, $type, $method) {
        //     // 重新加锁读，防止连点问题
        //     $order = Order::nopay()->where('order_sn', $order->order_sn)->lock(true)->find();
        //     if (!$order) {
        //         $this->error("订单已支付");
        //     }
        //     $total_fee = $order->total_fee;
        //
        //     // 扣除余额
        //     $user = User::info();
        //
        //     if (is_null($user)) {
        //         // 没有登录，请登录
        //         $this->error(__('Please login first'), null, 401);
        //     }
        //
        //     User::money(-$total_fee, $user->id, 'wallet_pay', $order->id, '',[
        //         'order_id' => $order->id,
        //         'order_sn' => $order->order_sn,
        //     ]);
        //
        //     // 支付后流程
        //     $notify = [
        //         'order_sn' => $order['order_sn'],
        //         'transaction_id' => '',
        //         'notify_time' => date('Y-m-d H:i:s'),
        //         'buyer_email' => $user->id,
        //         'pay_fee' => $order->total_fee,
        //         'pay_type' => 'wallet'             // 支付方式
        //     ];
        //     $notify['payment_json'] = json_encode($notify);
        //     $order->paymentProcess($order, $notify);
        //
        //     return $order;
        // });

        $this->success('支付成功', $order);
    }

    /**
     * 支付成功回调
     * @ApiInternal
     */
    public function notifyx()
    {
        Log::write('notifyx-comein:');

        $payment = $this->request->param('payment', 'wechat');
        $platform = $this->request->param('platform', 'wxMiniProgram');

        $pay = new \addons\drama\library\PayService($this->site_id, $payment, $platform);

        $result = $pay->notify(function ($data, $pay = null) use ($payment) {
            Log::write('notifyx-result:'. json_encode($data));
            try {
                $out_trade_no = $data['out_trade_no'];
                $out_refund_no = $data['out_biz_no'] ?? '';

                list($order, $prepay_type) = $this->getOrderInstance($out_trade_no);
                // 判断是否是支付宝退款（支付宝退款成功会通知该接口）
                if ($payment == 'alipay'    // 支付宝支付
                    && $data['notify_type'] == 'trade_status_sync'      // 同步交易状态
                    && $data['trade_status'] == 'TRADE_CLOSED'          // 交易关闭
                    && $out_refund_no                                   // 退款单号
                ) {
                    // 退款回调
                    if ($prepay_type == 'order') {
                        // 退款逻辑
                    } else {
                        // 其他订单如果支持退款，逻辑这里补充
                    }

                    return $this->payResponse($pay, $payment);
                }

                // 判断支付宝微信是否是支付成功状态，如果不是，直接返回响应
                if ($payment == 'alipay' && $data['trade_status'] != 'TRADE_SUCCESS') {
                    // 不是交易成功的通知，直接返回成功
                    return $this->payResponse($pay, $payment);
                }

                if ($payment == 'wechat' && ($data['result_code'] != 'SUCCESS' || $data['return_code'] != 'SUCCESS')) {
                    // 微信交易未成功，返回 false，让微信再次通知
                    return false;
                }

                // 支付成功流程
                $pay_fee = $payment == 'alipay' ? $data['total_amount'] : $data['total_fee'] / 100;


                //你可以在此编写订单逻辑
                $order = $order->where('order_sn', $out_trade_no)->find();

                if (!$order || $order->status > 0) {
                    // 订单不存在，或者订单已支付
                    return $this->payResponse($pay, $payment);
                }

                $notify = [
                    'order_sn' => $data['out_trade_no'],
                    'transaction_id' => $payment == 'alipay' ? $data['trade_no'] : $data['transaction_id'],
                    'notify_time' => date('Y-m-d H:i:s', strtotime($data['time_end'] ?? $data['notify_time'])),
                    'buyer_email' => $payment == 'alipay' ? $data['buyer_logon_id'] : $data['openid'],
                    'payment_json' => json_encode($data),
                    'pay_fee' => $pay_fee,
                    'pay_type' => $payment              // 支付方式
                ];
                $order->paymentProcess($order, $notify);

                return $this->payResponse($pay, $payment);
            } catch (\Exception $e) {
                Log::write('notifyx-error:' . json_encode($e->getMessage()));
            }
        });

        return $result;
    }

    /**
     * 退款成功回调
     * @ApiInternal
     */
    public function notifyr()
    {
        Log::write('notifyreturn-comein:');

        $payment = $this->request->param('payment');
        $platform = $this->request->param('platform');

        $pay = new \addons\drama\library\PayService($this->site_id, $payment, $platform);

        $result = $pay->notifyRefund(function ($data, $pay = null) use ($payment, $platform) {
            try {
                $out_refund_no = $data['out_refund_no'];
                $out_trade_no = $data['out_trade_no'];

                // 退款逻辑


                return $this->payResponse($pay, $payment);
            } catch (\Exception $e) {
                Log::write('notifyreturn-error:' . json_encode($e->getMessage()));
                return false;
            }
        });

        return $result;
    }

    /**
     * @ApiInternal
     */
    public function confirm(){
    }

    /**
     * 响应
     * @ApiInternal
     */
    private function payResponse($pay = null, $payment = null)
    {
        return $pay->success()->send();
    }


    /**
     * 根据订单号获取订单实例
     * @ApiInternal
     * @param [type] $order_sn
     * @return void
     */
    private function getOrderInstance($order_sn)
    {
        if (strpos($order_sn, 'TO') === 0) {
            // VIP订单
            $prepay_type = 'vip';
            $order = new VipOrder();
        } else if (strpos($order_sn, 'AO') === 0) {
            // 剧场积分充值订单
            $prepay_type = 'usable';
            $order = new UsableOrder();
        } else if (strpos($order_sn, 'MO') === 0) {
            // 剧场积分充值订单
            $prepay_type = 'mgg';
            $order = new MggOrder();
        } else {
            // 分销商订单
            $prepay_type = 'reseller';
            $order = new ResellerOrder();
        }

        return [$order, $prepay_type];
    }
}
