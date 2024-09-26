<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\library;

use Yansongda\Pay\Exceptions\GatewayException;
use Yansongda\Pay\Pay;
use Yansongda\Pay\Log;
use addons\drama\exception\Exception;

class PayService
{
    protected $site_id;
    protected $config;
    protected $platform;
    protected $payment;
    protected $notify_url;
    public $method;


    public function __construct($site_id, $payment, $platform = '', $notify_url = '', $type = 'pay')
    {
        $this->site_id = $site_id;
        $this->platform = $platform;
        $this->payment = $payment;
        $this->notify_url = $notify_url;
        $this->type = $type;
        $this->setPaymentConfig();
    }

    private function setPaymentConfig()
    {
        $paymentConfig = json_decode(\addons\drama\model\Config::get(['name' => $this->payment, 'site_id'=>$this->site_id])->value, true);

        // 如果是支付，并且不是 复制地址的支付宝支付
        if ($this->type == 'pay' && $this->platform != 'url' && !in_array($this->platform, $paymentConfig['platform'])) {
            new Exception('暂不支持该方式付款');
        }

        $this->config = $paymentConfig;
        $this->config['notify_url'] = $this->notify_url;

        $this->config['http'] = [
            'timeout' => 10,
            'connect_timeout' => 10,
        ];

        if ($this->payment === 'wechat') {
            // 根据不同平台设置相应的 appid
            $this->setWechatAppId();
        }

        // 设置支付证书路径
        $this->setCert();
    }

    private function setWechatAppId()
    {
        switch ($this->platform) {
            case 'wxOfficialAccount':
                $platformConfig = json_decode(\addons\drama\model\Config::get(['name' => $this->platform, 'site_id'=>$this->site_id])->value, true);
                if (isset($this->config['mode']) && $this->config['mode'] === 'service') {
                    $this->config['sub_app_id'] = $platformConfig['app_id'];
                    $this->config['app_id'] = $this->config['app_id'];      // 主商户号，关联的 app_id
                } else {
                    $this->config['app_id'] = $platformConfig['app_id'];
                }
                break;
            case 'wxMiniProgram':
                $platformConfig = json_decode(\addons\drama\model\Config::get(['name' => $this->platform, 'site_id'=>$this->site_id])->value, true);
                if (isset($this->config['mode']) && $this->config['mode'] === 'service') {
                    $this->config['sub_miniapp_id'] = $platformConfig['app_id'];
                    // $this->config['sub_app_id'] = $platformConfig['app_id'];
                    $this->config['miniapp_id'] = $this->config['app_id'];      // 主商户号，关联的 app_id
                } else {
                    $this->config['miniapp_id'] = $platformConfig['app_id'];
                    $this->config['app_id'] = $platformConfig['app_id'];        // 小程序微信企业付款
                }
                break;
            case 'H5':
                $platformConfig = json_decode(\addons\drama\model\Config::get(['name' => $this->platform, 'site_id'=>$this->site_id])->value, true);
                if (isset($this->config['mode']) && $this->config['mode'] === 'service') {
                    $this->config['sub_app_id'] = $platformConfig['app_id'];
                    $this->config['appid'] = $this->config['app_id'];      // 主商户号，关联的 app_id
                } else {
                    $this->config['app_id'] = $platformConfig['app_id'];
                }
                break;
            case 'Web':
                $platformConfig = json_decode(\addons\drama\model\Config::get(['name' => $this->platform, 'site_id'=>$this->site_id])->value, true);
                if (isset($this->config['mode']) && $this->config['mode'] === 'service') {
                    // $this->config['sub_app_id'] = $platformConfig['app_id'];
                    $this->config['appid'] = $this->config['app_id'];      // 主商户号，关联的 app_id
                } else {
                    $this->config['app_id'] = $platformConfig['app_id'];
                }
                break;
            case 'App':
                $platformConfig = json_decode(\addons\drama\model\Config::get(['name' => 'App', 'site_id'=>$this->site_id])->value, true);
                if (isset($this->config['mode']) && $this->config['mode'] === 'service') {
                    $this->config['sub_appid'] = $platformConfig['app_id'];
                    $this->config['sub_app_id'] = $platformConfig['app_id'];
                    $this->config['appid'] = $this->config['app_id'];      // 主商户号，关联的 app_id
                } else {
                    $this->config['appid'] = $platformConfig['app_id'];         // 微信 App 支付使用这个
                    $this->config['app_id'] = $platformConfig['app_id'];        // 微信 App 支付退款使用的是这个
                }

                break;
        }
    }


    // 处理证书路径
    private function setCert()
    {
        // 处理证书路径
        if ($this->payment == 'wechat') {
            // 微信支付证书
            $this->config['cert_client'] = ROOT_PATH . 'public' . $this->config['cert_client'];
            $this->config['cert_key'] = ROOT_PATH . 'public' . $this->config['cert_key'];
        } else {
            // 支付宝证书路径
            $end = substr($this->config['ali_public_key'], -4);
            if ($end == '.crt') {
                $this->config['ali_public_key'] = ROOT_PATH . 'public' . $this->config['ali_public_key'];
            }
            $this->config['app_cert_public_key'] = ROOT_PATH . 'public' . $this->config['app_cert_public_key'];
            $this->config['alipay_root_cert'] = ROOT_PATH . 'public' . $this->config['alipay_root_cert'];
        }
    }


    private function setPaymentMethod()
    {
        $method = [
            'wechat' => [
                'wxOfficialAccount' => 'mp',   //公众号支付 Collection
                'wxMiniProgram' => 'miniapp', //小程序支付
                'H5' => 'wap', //手机网站支付 Response
                'Web' => 'scan', //扫码支付
                'App' => 'app' //APP 支付 JsonResponse
            ],
            'alipay' => [
                'wxOfficialAccount' => 'wap',   //手机网站支付 Response
                'wxMiniProgram' => 'wap', //小程序支付
                'H5' => 'wap', //手机网站支付 Response
                'url' => 'wap', //手机网站支付 Response
                'Web' => 'scan', // 扫码支付
                'App' => 'app' //APP 支付 JsonResponse
            ],
        ];

        $this->method = $method[$this->payment][$this->platform];
    }

    public function create($order)
    {
        //        $order = [
        //            'out_trade_no' => time(),
        //            'total_fee' => '1', // **单位：分**
        //            'body' => 'test body - 测试',
        //            'openid' => 'onkVf1FjWS5SBIixxxxxxx', //微信需要带openid过来
        //        ];

        // 设置支付方式
        $this->setPaymentMethod();

        $method = $this->method;
        switch ($this->payment) {
            case 'wechat':
                if (isset($this->config['mode']) && $this->config['mode'] === 'service') {
                    $order['sub_openid'] = $order['openid'] ?? '';
                    unset($order['openid']);
                }
                $order['total_fee'] = $order['total_fee'] * 100;
                // 修复新商户报错问题
                unset($order['order_id']);
                $pay = Pay::wechat($this->config)->$method($order);

                break;
            case 'alipay':
                if (in_array($this->platform, ['wxOfficialAccount', 'wxMiniProgram', 'H5'])) {
                    // 返回支付宝支付链接
                    $pay = request()->domain() . '/addons/drama/pay/alipay?order_sn=' . $order['out_trade_no'];
                } else {
                    if ($this->method == 'wap') {
                        // 支付宝 wap 支付，增加 return_url
                        // 获取 h5 域名
                        $platformConfig = json_decode(\addons\drama\model\Config::get(['name' => 'drama', 'site_id'=>$this->site_id])->value, true);
                        // 如果域名存在，增加 return_url
                        if ($platformConfig && isset($platformConfig['domain'])) {
                            $start = substr($platformConfig['domain'], -1) == '/' ? "" : "/";
                            $orderType = strpos($order['out_trade_no'], 'TO') === 0 ? 'recharge' : 'goods';
                            $this->config['return_url'] = $platformConfig['domain'] . $start . "pages/order/payment/result?orderId=" . $order['order_id'] . "&type=alipay&payState=success&orderType=" . $orderType;
                        }
                    }

                    $pay = Pay::alipay($this->config)->$method($order);
                }

                break;
        }

        return $pay;
    }

    public function checkPay($orderid){
        $pay = $this->getPay();
        try {
            $result = $pay->find($orderid, 'scan');
            if($this->payment == 'wechat'){
                if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                    return ['status' => $result->trade_state];
                }
            }elseif($this->payment == 'alipay'){
                if ($result['code'] == '10000' && $result['trade_status'] == 'TRADE_SUCCESS') {
                    return ['status' => $result->trade_state];
                }
            }
        } catch (GatewayException $e) {
        }
        return false;
    }

    // 企业付款
    public function transfer($payload)
    {
        // 服务商模式企业付款使用子商户证书
        if ($this->payment == 'wechat' && isset($this->config['mode']) && $this->config['mode'] === 'service') {
            $this->config['cert_client'] = ROOT_PATH . 'public' . $this->config['sub_cert_client'];
            $this->config['cert_key'] = ROOT_PATH . 'public' . $this->config['sub_cert_key'];
            $this->config['key'] = $this->config['sub_key'];
            $this->config['mch_id'] = $this->config['sub_mch_id'];
            $this->config['mode'] = 'normal';       // 临时改为普通商户
        }

        $code = 0;
        $response = [];
        switch ($this->payment) {
            case 'wechat':
                $payload['amount'] = $payload['amount'] * 100;
                $response = Pay::wechat($this->config)->transfer($payload);
                if ($response['return_code'] === 'SUCCESS' && $response['result_code'] === 'SUCCESS') {
                    $code = 1;
                }
                break;
            case 'alipay':
                $response = Pay::alipay($this->config)->transfer($payload);
                if ($response['code'] === '10000' && $response['status'] === 'SUCCESS') {
                    $code = 1;
                }
                break;
        }

        return [$code, $response];
    }


    public function notify($callback)
    {
        $pay = $this->getPay();

        try {
            $data = $pay->verify(); // 是的，验签就这么简单！

            // $data = '{"appid":"wxa69d463d897f4b9e","bank_type":"OTHERS","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1644030144","nonce_str":"p4M0KK8g1p1TPPd9","openid":"oFqCD6mtBoT60sgU4Q8pBs5_5Fk4","out_trade_no":"AO202302305275148419048700","result_code":"SUCCESS","return_code":"SUCCESS","sign":"4327D41139DCBD98167C9BCDCD17561A","time_end":"20230612143105","total_fee":"1","trade_type":"NATIVE","transaction_id":"4200001849202306129720100017"}';
            // $data = json_decode($data, true);
            $result = $callback($data, $pay);

            // Log::debug('Wechat notify', $data->all());
        } catch (\Exception $e) {
            \think\Log::error('notify-error:' . $e->getMessage());
            // $e->getMessage();
        }

        return $result;
    }


    public function refund($order_data)
    {
        $pay = $this->getPay();

        $order_data['type'] = $this->platform == 'wxMiniProgram' ? 'miniapp' : '';

        $result = $pay->refund($order_data);

        \think\Log::write('refund-result' . json_encode($result));

        if ($this->payment == 'wechat') {
            // 微信通知回调 pay->notifyr
            if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
                return $result;
            } else {
                throw new \Exception($result['return_msg']);
            }
        } else {
            // 支付宝通知回调 pay->notifyx
            if ($result['code'] == "10000") {
                return $result;
            } else {
                throw new \Exception($result['msg']);
            }
        }
    }


    public function notifyRefund($callback)
    {
        $pay = $this->getPay();

        try {
            $data = $pay->verify(null, true); // 是的，验签就这么简单！
            \think\Log::write('notifyr-result:' . json_encode($data));

            $result = $callback($data, $pay);

            // Log::debug('Wechat notify', $data->all());
        } catch (\Exception $e) {
            \think\Log::write('notifyr-verify-error:' . $e->getMessage()); // $e->getMessage();

            return false;
        }

        return $result;
    }


    private function getPay()
    {
        switch ($this->payment) {
            case 'wechat':
                $pay = Pay::wechat($this->config);
                break;
            case 'alipay':
                $pay = Pay::alipay($this->config);
                break;
            default:
                new Exception('支付方式不支持');
        }

        return $pay;
    }


}
