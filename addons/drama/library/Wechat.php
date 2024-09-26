<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\library;

use EasyWeChat\Factory;
use addons\drama\model\Config;
use think\Model;
use fast\Http;

/**
 *
 */
class Wechat extends Model
{
    protected $config;
    protected $app;


    public function __construct($platform, $sign=null)
    {
        $this->setConfig($platform, $sign);
        switch ($platform) {
            case 'wxOfficialAccount':
                $this->app    = Factory::officialAccount($this->config);
                break;
            case 'wxMiniProgram':
                $this->app    = Factory::miniProgram($this->config);
                break;
            case 'openPlatform':
                $this->app    = Factory::openPlatform($this->config);
                break;
        }
    }

    // 返回实例
    public function getApp() {
        return $this->app;
    }

    //小程序:获取openid&session_key
    public function code($code)
    {
        return $this->app->auth->session($code);
    }

    public function oauth()
    {
        $oauth = $this->app->oauth;
        return $oauth;
    }

    //解密信息
    public function decryptData($session, $iv, $encryptData)
    {
        $data = $this->app->encryptor->decryptData($session, $iv, $encryptData);

        return $data;
    }

    public function unify($orderBody)
    {
        $result = $this->app->order->unify($orderBody);
        return $result;
    }

    public function bridgeConfig($prepayId)
    {
        $jssdk = $this->app->jssdk;
        $config = $jssdk->bridgeConfig($prepayId, false);
        return $config;
    }

    public function notify()
    {
        $result = $this->app;
        return $result;
    }

    //获取accessToken
    public function getAccessToken()
    {
        $accessToken = $this->app->access_token;
        $token = $accessToken->getToken(); // token 数组  token['access_token'] 字符串
        //$token = $accessToken->getToken(true); // 强制重新从微信服务器获取 token.
        return $token;
    }


    /**
     * 重写 jssdk buildConfig 方法
     *
     * @param [type] $jssdk jssdk 实例
     * @param [type] $apis  要请求的 api 列表
     * @param boolean $debug    debug 
     * @param boolean $beta 
     * @param boolean $json 是否返回 json
     * @param array $openTagList    开放标签列表
     * @return void
     */
    public function buildConfig($jssdk, $jsApiList, $debug = false, $beta = false, $json = false, $openTagList = [], $url = '')
    {
        $url = $url ?: $jssdk->getUrl();
        $nonce = \EasyWeChat\Kernel\Support\Str::quickRandom(10);
        $timestamp = time();

        $signature = [
            'appId' => $this->config['app_id'],
            'nonceStr' => $nonce,
            'timestamp' => $timestamp,
            'url' => $url,
            'signature' => $jssdk->getTicketSignature($jssdk->getTicket()['ticket'], $nonce, $timestamp, $url),
        ];

        $config = array_merge(compact('debug', 'beta', 'jsApiList', 'openTagList'), $signature);

        return $json ? json_encode($config) : $config;
    }


    public function sendTemplateMessage($attributes)
    {
        extract($attributes);
        $this->app->template_message->send([
            'touser' => $openId,
            'template_id' => $templateId,
            'page' => $page,
            'form_id' => $formId,
            'data' => $data,
            'emphasis_keyword' => $emphasis_keyword
        ]);
    }


    /**
     * 发送公众号订阅消息
     *
     * @return void
     */
    public function bizsendSubscribeMessage($data) {
        $access_token = $this->getAccessToken();

        $bizsendUrl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend?access_token={$access_token['access_token']}";

        $headers = ['Content-type: application/json'];
        $options = [
            CURLOPT_HTTPHEADER => $headers
        ];
        $result = Http::sendRequest($bizsendUrl, json_encode($data), 'POST', $options);

        if (isset($result['ret']) && $result['ret']) {
            // 请求成功
            $result = json_decode($result['msg'], true);
            
            return $result;
        }
        
        // 请求失败
        return ['errcode' => -1, 'msg' => $result];
    }

    public function menu($act = 'create', $buttons = '')
    {
        $result = $this->app->menu->$act($buttons);
        return $result;

    }

    public function getUserInfoByOpenId(string $openId)
    {
        $result = $this->app->user->get($openId);
        return $result;
    }


    /**
     * 合并默认配置
     * @param [type] $platform
     * @return void
     */
    private function setConfig($platform, $sign) {
        $debug = config('app_debug');

        $defaultConfig = [
            'log' => [
                'default' => $debug ? 'dev' : 'prod', // 默认使用的 channel，生产环境可以改为下面的 prod
                'channels' => [
                    // 测试环境
                    'dev' => [
                        'driver' => 'single',
                        'path' => '/tmp/easywechat.log',
                        'level' => 'debug',
                    ],
                    // 生产环境
                    'prod' => [
                        'driver' => 'daily',
                        'path' => '/tmp/easywechat.log',
                        'level' => 'info',
                    ],
                ],
            ],
        ];

        // 获取对应平台的配置
        $this->config = Config::getEasyWechatConfig($platform, $sign);
        // 根据框架 debug 合并 log 配置
        $this->config = array_merge($this->config, $defaultConfig);
    }

    /**
     * @param string $openid
     * @param string $content
     * @return mixed|string
     * 文本内容安全识别
     */
    public function msgSecCheck($openid = '', $content = '')
    {
        try {
            $access_token = $this->getAccessToken();
            $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token=' . $access_token['access_token'];
            $post = [
                'content' => $content,
                'version' => 2,
                'scene' => 1,
                'openid' => $openid
            ];
            $result = Http::sendRequest($url, json_encode($post));
            $result = $result['msg'];
            $result = @json_decode($result, true);
            if($result['errcode'] != 0) {
                return true;
            }
            if (!isset($result['result'])) {
                return true;
            }
            return $result['result']['suggest'] == 'pass';
        } catch (\Exception $e) {
            return true;
        }
    }

}
