<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\library\Wechat as WechatLibrary;
use addons\drama\model\Config;
use addons\drama\model\Wechat as WechatModel;
use app\admin\model\drama\MggOrder;
use EasyWeChat\Kernel\Exceptions\HttpException;
use think\Cache;
use addons\drama\model\ResellerOrder;
use addons\drama\model\UsableOrder;
use addons\drama\model\VipOrder;
use think\Log;

/**
 * 微信接口
 * @ApiInternal
 */
class Wechat extends Base
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $app = null;
    protected $userOpenId = '';

    /**
     * 微信公众号服务端API对接、处理消息回复
     */
    public function index()
    {
        if (isset($_GET['echostr'])) {
            $this->valid();
        }
        $sign = request()->param('sign');
        if(request()->param('type',false)){
            $wechat = new WechatLibrary('wxMiniProgram', $sign);
        }else{
            $wechat = new WechatLibrary('wxOfficialAccount', $sign);
        }
        $this->app = $wechat->getApp();
        $this->app->server->push(function ($message) {
            //初始化信息
            $this->userOpenId = $message['FromUserName'];
            // return json_encode($message, JSON_UNESCAPED_UNICODE); //调试使用

            switch ($message['MsgType']) {
                case 'event': //收到事件消息
                    switch ($message['Event']) {
                        case 'subscribe': //订阅（关注）事件
                            // 关注回复 使用客服接口回复
                            $subscribe = WechatModel::get(['type' => 'subscribe','site_id' => $this->site_id]);
                            if ($subscribe) {
                                $sub_content = json_decode($subscribe['content'], true);
                                switch ($sub_content['type']) {
                                    case 'text':  //回复文本
                                        $sub_content = new \EasyWeChat\Kernel\Messages\Text($sub_content['content']);
                                        $this->app->customer_service->message($sub_content)->to($this->userOpenId)->send();
                                        break;
                                    case 'image': //回复图片
                                        $sub_content = new \EasyWeChat\Kernel\Messages\Image($sub_content['media_id']);
                                        $this->app->customer_service->message($sub_content)->to($this->userOpenId)->send();
                                        break;
                                    case 'news': //回复图文
                                        $sub_content = new \EasyWeChat\Kernel\Messages\Media($sub_content['media_id'], 'mpnews');
                                        $this->app->customer_service->message($sub_content)->to($this->userOpenId)->send();
                                        break;
                                    case 'voice': //回复语音
                                        $sub_content = new \EasyWeChat\Kernel\Messages\Voice($sub_content['media_id']);
                                        $this->app->customer_service->message($sub_content)->to($this->userOpenId)->send();
                                        break;
                                    case 'video': //回复视频
                                        $sub_content = new \EasyWeChat\Kernel\Messages\Video($sub_content['media_id']);
                                        $this->app->customer_service->message($sub_content)->to($this->userOpenId)->send();
                                        break;
                                    case 'link': //回复链接
                                        $items = new  \EasyWeChat\Kernel\Messages\NewsItem([
                                            'title'       => $sub_content['title'],
                                            'description' => $sub_content['description'],
                                            'url'         => $sub_content['url'],
                                            'image'       => cdnurl($sub_content['image'], true),
                                        ]);
                                        $sub_content = new \EasyWeChat\Kernel\Messages\News([$items]);
                                        $this->app->customer_service->message($sub_content)->to($this->userOpenId)->send();
                                }
                            }
                            // 授权登录需要发送授权事件
                            if($message['EventKey'] == null){
                                return '';
                            }
                            return $this->response($message, 'subscribe');
                            break;
                        case 'unsubscribe': //取消订阅（关注）事件
                            break;
                        case 'CLICK':  //自定义菜单事件
                            return $this->response($message, 'CLICK');
                            break;
                        case 'SCAN': //扫码事件
                            return $this->response($message, 'SCAN');
                            break;
                        case 'xpay_goods_deliver_notify':

                            Log::write('notifyx-result:'. json_encode($message),'xunipay');

                            try {
                                $out_trade_no = $message['OutTradeNo'];

                                if (strpos($out_trade_no, 'TO') === 0) {
                                    // VIP订单
                                    $prepay_type = 'vip';
                                    $order = new VipOrder();
                                } else if (strpos($out_trade_no, 'AO') === 0) {
                                    // 剧场积分充值订单
                                    $prepay_type = 'usable';
                                    $order = new UsableOrder();
                                } else if (strpos($out_trade_no, 'MO') === 0) {
                                    // 免广告订单
                                    $prepay_type = 'mgg';
                                    $order = new MggOrder();
                                } else {
                                    // 分销商订单
                                    $prepay_type = 'reseller';
                                    $order = new ResellerOrder();
                                }

                                $order = $order->where('order_sn', $out_trade_no)->find();

                                //Log::write('notifyx-result:'. json_encode($order),'xunipay');

                                if (!$order || $order->status > 0) {
                                    // 订单不存在，或者订单已支付
                                    echo json_encode(['ErrCode' => 0]);
                                    exit;
                                }

                                $notify = [
                                    'order_sn' => $message['OutTradeNo'],
                                    'transaction_id' => $message['WeChatPayInfo']['TransactionId'],
                                    'notify_time' => date('Y-m-d H:i:s', $message['CreateTime']),
                                    'buyer_email' => $message['OpenId'],
                                    'payment_json' => json_encode($message),
                                    'pay_fee' => bcdiv($message['GoodsInfo']['ActualPrice'],100,2),
                                    'pay_type' => 'wechat'              // 支付方式
                                ];
                                $order->paymentProcess($order, $notify);
                                echo json_encode(['ErrCode' => 0]);

                            }catch(\Exception $e){
                                Log::write('notifyx-error:' . json_encode($e->getMessage()),'xunipay');
                            }

                            exit;

                            break;
                    }
                    break;
                case 'text': //收到文本消息
                    //检测关键字回复
                    $content = $message['Content'];
                    $auto_reply = WechatModel::where('site_id', $this->site_id)
                        ->where('type', 'auto_reply')
                        ->where('find_in_set(:keywords,rules)', ['keywords' => $content])
                        ->find();
                    if ($auto_reply) {
                        return $this->response($auto_reply);
                    }
                case 'image': //收到图片消息
                case 'voice': //收到语音消息
                case 'video': //收到视频消息
                case 'location': //收到坐标消息
                case 'link': //收到链接消息
                case 'file': //收到文件消息
                default: // ... 默认回复消息
                    $default_reply = WechatModel::where('site_id', $this->site_id)->where('type', 'default_reply')->find();
                    if ($default_reply) {
                        return $this->response($default_reply);
                    }
            }
        });
        $response = $this->app->server->serve();
        // 将响应输出
        $response->send();
    }

    /**
     * 微信小程序码
     */
    public function wxacode()
    {
        $scene = $this->request->get('scene', '');
        $path = $this->request->get('path', '');

        if (empty($path)) {
            $path = 'pages/home/index';
        }

        try {
            $wechat = new WechatLibrary('wxMiniProgram');
            $content = $wechat->getApp()->app_code->getUnlimit($scene, [
                'page' => $path,
                'is_hyaline' => true,
            ]);
        }catch (HttpException $e){
            $this->error('小程序配置信息错误:'.$e->getMessage());
        }

        if ($content instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            return response($content->getBody(), 200, ['Content-Length' => strlen($content)])->contentType('image/png');
        } else {
            // 小程序码获取失败
            $msg = isset($content['errcode']) ? $content['errcode'] : '-';
            $msg .= isset($content['errmsg']) ? $content['errmsg'] : '';
            \think\Log::write('wxacode-error' . $msg);

            $this->error('获取失败', $msg);
        }
    }

    /**
     *
     */
    public function jssdk()
    {
        $params = $this->request->post();
        $apis = [
            'checkJsApi',
            'updateTimelineShareData',
            'updateAppMessageShareData',
            "onMenuShareAppMessage",
            "onMenuShareTimeline",
            'getLocation', //获取位置
            'openLocation', //打开位置
            'scanQRCode', //扫一扫接口
            'chooseWXPay', //微信支付
            'chooseImage', //拍照或从手机相册中选图接口
            'previewImage', //预览图片接口       'uploadImage', //上传图片
            'openAddress',   // 获取微信地址
        ];
        // $openTagList = [
        //     'wx-open-subscribe'
        // ];

        $uri = urldecode($params['uri']);

        try {
            $wechat = new WechatLibrary('wxOfficialAccount');

            $jssdk = $wechat->getApp()->jssdk->setUrl($uri);
            // easywechat 版本 < 4.2.33 的 buildConfig 方法 没有 openTagList 参数，手动覆盖底层 buildConfig 方法
            $res = $wechat->buildConfig($jssdk, $apis, $debug = false, $beta = false, $json = false);
        }catch (HttpException $e){
            $this->error('公众号配置信息错误:'.$e->getMessage());
        }

        $this->success('sdk', $res);
    }


    //签名验证公共接口
    private function valid()
    {
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $config = Config::getEasyWechatConfig('wxOfficialAccount');
        $token = $config['token'];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 回复消息
     */
    private function response($replyInfo, $event='text')
    {
        switch ($event) {
            case 'subscribe':
                $replyInfo['EventKey'] = str_replace('qrscene_', '', $replyInfo['EventKey']);
                $key = $replyInfo['EventKey'];
                if(Cache::has($key)){
                    $time = @json_decode(Cache::get($key), true)['time'];
                    $expire = $time - time();
                    if($expire > 0){
                        $key_data = [
                            'sao'=>1,
                            'auth'=>0,
                            'register'=>0,
                            'openid'=>$this->userOpenId,
                            'data'=>[],
                            'time'=>$time
                        ];
                        Cache::set($key, json_encode($key_data), $expire);
                    }else{
                        $message = [
                            'type'        => 'text',
                            'content'       => '授权已失效，请刷新后重新扫码',
                        ];
                        break;
                    }
                }else{
                    $message = [
                        'type'        => 'text',
                        'content'       => '授权已失效，请刷新后重新扫码',
                    ];
                    break;
                }
                $sign = request()->param('sign');
                $url = urlencode(request()->domain().'/addons/drama/user/wxOfficialAccountLogin?key='.$key.'&sign='.$sign);
                $config = Config::getEasyWechatConfig('wxOfficialAccount');
                $oauthUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$config['app_id']}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state=1";
                $configModel = new \addons\drama\model\Config;
                $system = $configModel->where('site_id', $this->site_id)->where('name', 'drama')->value('value');
                // 基本设置
                $system = json_decode($system, true);
                $logo = (isset($system['logo']) && $system['logo']) ? cdnurl($system['logo'], true) :  cdnurl('/assets/img/logo.png', true);
                $message = [
                    'type'        => 'link',
                    'title'       => '登陆授权',
                    'description' => '请点击授权微信号登陆，如不是自己操作请忽略！',
                    'url'         => $oauthUrl,
                    'image'       => $logo,
                ];
                break;
            case 'SCAN': //解析扫码事件EventKey
                $key = $replyInfo['EventKey'];
                if(Cache::has($key)){
                    $time = @json_decode(Cache::get($key), true)['time'];
                    $expire = $time - time();
                    if($expire > 0){
                        $key_data = [
                            'sao'=>1,
                            'auth'=>0,
                            'register'=>0,
                            'openid'=>$this->userOpenId,
                            'data'=>[],
                            'time'=>$time
                        ];
                        Cache::set($key, json_encode($key_data), $expire);
                    }else{
                        $message = [
                            'type'        => 'text',
                            'content'       => '授权已失效，请刷新后重新扫码',
                        ];
                        return $this->response($message);
                    }
                }else{
                    $message = [
                        'type'        => 'text',
                        'content'       => '授权已失效，请刷新后重新扫码',
                    ];
                    break;
                }
                $sign = request()->param('sign');
                $url = urlencode(request()->domain().'/addons/drama/user/wxOfficialAccountLogin?key='.$key.'&sign='.$sign);
                $config = Config::getEasyWechatConfig('wxOfficialAccount');
                $oauthUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$config['app_id']}&redirect_uri={$url}&response_type=code&scope=snsapi_userinfo&state=1";
                $configModel = new \addons\drama\model\Config;
                $system = $configModel->where('site_id', $this->site_id)->where('name', 'drama')->value('value');
                // 基本设置
                $system = json_decode($system, true);
                $logo = (isset($system['logo']) && $system['logo']) ? cdnurl($system['logo'], true) :  cdnurl('/assets/img/logo.png', true);
                $message = [
                    'type'        => 'link',
                    'title'       => '登陆授权',
                    'description' => '请点击授权微信号登陆，如不是自己操作请忽略！',
                    'url'         => $oauthUrl,
                    'image'       => cdnurl($logo, true),
                ];
                break;
            case 'CLICK': //解析菜单点击事件EventKey
                $key = explode('|', $replyInfo['EventKey']);
                if ($key) {
                    $message['type'] = $key[0];
                    if ($key[0] === 'text') {
                        $message['content'] =  json_decode(WechatModel::get($key[1])->content, true);
                    } elseif($key[0] === 'link') {
                        $link = WechatModel::get($key[1]);
                        $message = array_merge($message, json_decode($link->content, true));
                        $message['title'] = $link->name;
                        // return json_encode($message);
                    }else {
                        $message['media_id'] = $key[1];
                    }
                }
                break;
            default:
                $message = json_decode($replyInfo['content'], true);
                break;
        }

        switch ($message['type']) {
            case 'text':  //回复文本
                $content = new \EasyWeChat\Kernel\Messages\Text($message['content']);
                break;
            case 'image': //回复图片
                $content = new \EasyWeChat\Kernel\Messages\Image($message['media_id']);
                break;
            case 'news': //回复图文
                $message = new \EasyWeChat\Kernel\Messages\Media($message['media_id'], 'mpnews');
                $this->app->customer_service->message($message)->to($this->userOpenId)->send();  //素材消息使用客服接口回复
                break;
            case 'voice': //回复语音
                $content = new \EasyWeChat\Kernel\Messages\Voice($message['media_id']);
                break;
            case 'video': //回复视频
                $content = new \EasyWeChat\Kernel\Messages\Video($message['media_id']);
                break;
            case 'link': //回复链接
                $items = new  \EasyWeChat\Kernel\Messages\NewsItem([
                    'title'       => $message['title'],
                    'description' => $message['description'],
                    'url'         => $message['url'],
                    'image'       => cdnurl($message['image'], true),
                ]);
                $content = new \EasyWeChat\Kernel\Messages\News([$items]);
                break;
        }

        return $content;
    }

}
