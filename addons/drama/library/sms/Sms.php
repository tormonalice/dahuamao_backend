<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2015 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: yunwuxin <448901948@qq.com>
// +----------------------------------------------------------------------

namespace addons\drama\library\sms;

use addons\drama\library\sms\src\Alisms;
use addons\drama\library\sms\src\Hwsms;
use addons\drama\library\sms\src\SmsSingleSender;
use addons\drama\library\sms\src\Smsbao;

class Sms
{
    public $channel = 'alisms'; //渠道
    public $config = array(); //配置
    private $sendError = '';

    /**
     * Drama constructor.
     */
    public function __construct($channel, $config=array())
    {
        $this->channel = $channel;
        if(isset($config['template'])){
            $template = [];
            foreach ($config['template'] as $value){
                $template[$value['key']] = $value['value'];
            }
            $config['template'] = $template;
        }
        $this->config = $config;
    }

    /**
     * 短信发送行为
     * @param array $params 必须包含mobile,event,code
     * @return bool|mixed
     */
    public function send($params){
        if($this->channel == 'alisms'){
            $response = $this->aliSend($params);
        }elseif($this->channel == 'hwsms'){
            $response = $this->hwSend($params);
        }elseif($this->channel == 'qcloudsms'){
            $response = $this->qcloudSend($params);
        }elseif($this->channel == 'baosms'){
            $response = $this->baoSend($params);
        }else{
            $this->setError('参数错误');
            $response = false;
        }
        return $response;
    }

    /**
     * 短信发送通知
     * @param array $params 必须包含 mobile,event,msg
     * @return bool|mixed
     */
    public function notice($params){
        if($this->channel == 'alisms'){
            $response = $this->aliSend($params);
        }elseif($this->channel == 'hwsms'){
            $response = $this->hwSend($params);
        }elseif($this->channel == 'qcloudsms'){
            $response = $this->qcloudSend($params);
        }elseif($this->channel == 'baosms'){
            $response = $this->baoSend($params);
        }else{
            $this->setError('参数错误');
            $response = false;
        }
        return $response;
    }

    /**
     * 短信发送行为
     * @param array $params 必须包含mobile,event,code
     * @return  boolean
     */
    private function aliSend($params)
    {
        if (!isset($this->config['template'][$params['event']])) {
            $this->setError('请先配置短信模板');
            return false;
        }
        $alisms = new Alisms($this->config);
        $result = $alisms->mobile($params['mobile'])
            ->template($this->config['template'][$params['event']])
            ->param(['code' => $params['code']])
            ->send();
        if(!$result){
            $this->setError($alisms->getError());
        }
        return $result;
    }

    /**
     * 短信发送通知
     * @param array $params 必须包含 mobile,event,msg
     * @return  boolean
     */
    private function aliNotice($params)
    {
        $alisms = Alisms::instance();
        if (isset($params['msg'])) {
            if (is_array($params['msg'])) {
                $param = $params['msg'];
            } else {
                parse_str($params['msg'], $param);
            }
        } else {
            $param = [];
        }
        $param = $param ? $param : [];
        $params['template'] = isset($params['template']) ? $params['template'] : (isset($params['event']) && isset($this->config['template'][$params['event']]) ? $this->config['template'][$params['event']] : '');
        $result = $alisms->mobile($params['mobile'])
            ->template($params['template'])
            ->param($param)
            ->send();
        return $result;
    }

    /**
     * 短信发送行为
     * @param array $params 必须包含mobile,event,code
     * @return  boolean
     */
    private function hwSend($params)
    {
        if (!isset($this->config['template'][$params['event']])) {
            $this->setError('请先配置短信模板');
            return false;
        }
        $hwsms = new Hwsms($this->config);
        $result = $hwsms->mobile($params['mobile'])
            ->template($this->config['template'][$params['event']])
            ->param(['code' => $params['code']])
            ->send();
        if(!$result){
            $this->setError($hwsms->getError());
        }
        return $result;
    }

    /**
     * 短信发送通知
     * @param array $params 必须包含 mobile,event,msg
     * @return  boolean
     */
    private function hwNotice($params)
    {
        $hwsms = Hwsms::instance();
        if (isset($params['msg'])) {
            if (is_array($params['msg'])) {
                $param = $params['msg'];
            } else {
                parse_str($params['msg'], $param);
            }
        } else {
            $param = [];
        }
        $param = $param ? $param : [];
        $params['template'] = isset($params['template']) ? $params['template'] : (isset($params['event']) && isset($this->config['template'][$params['event']]) ? $this->config['template'][$params['event']] : '');
        $result = $hwsms->mobile($params['mobile'])
            ->template($params['template'])
            ->param($param)
            ->send();

        return $result;
    }

    /**
     * 短信发送行为
     * @param Sms $params
     * @return  boolean
     */
    private function qcloudSend($params)
    {
        try {
            if ($this->config['isTemplateSender'] == 1) {
                if (!isset($this->config['template'][$params['event']])) {
                    $this->setError('请先配置短信模板');
                    return false;
                }
                $templateID = $this->config['template'][$params['event']];
                //普通短信发送
                $sender = new SmsSingleSender($this->config['appid'], $this->config['appkey']);
                $result = $sender->sendWithParam("86", $params['mobile'], $templateID, ["{$params->code}"], $this->config['sign'], "", "");
            } else {
                $sender = new SmsSingleSender($this->config['appid'], $this->config['appkey']);
                //参数：短信类型{1营销短信，0普通短信 }、国家码、手机号、短信内容、扩展码（可留空）、服务的原样返回的参数
                $result = $sender->send($params['type'], '86', $params['mobile'], $params['msg'], "", "");
            }
            $rsp = json_decode($result, true);
            if ($rsp['result'] == 0 && $rsp['errmsg'] == 'OK') {
                return true;
            } else {
                //记录错误信息
                $this->setError($rsp);
                return false;
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
        }
        return false;
    }

    /**
     * 短信发送通知
     * @param array $params
     * @return  boolean
     */
    private function qcloudNotice($params)
    {
        try {
            if ($this->config['isTemplateSender'] == 1) {
                $templateID = $this->config['template'][$params['template']];
                //普通短信发送
                $sender = new SmsSingleSender($this->config['appid'], $this->config['appkey']);
                $result = $sender->sendWithParam("86", $params['mobile'], $templateID, ["{$params['msg']}"], $this->config['sign'], "", "");
            } else {
                $sender = new SmsSingleSender($this->config['appid'], $this->config['appkey']);
                //参数：短信类型{1营销短信，0普通短信 }、国家码、手机号、短信内容、扩展码（可留空）、服务的原样返回的参数
                $result = $sender->send($params['type'], '86', $params['mobile'], $params['msg'], "", "");
            }
            $rsp = (array)json_decode($result, true);
            if ($rsp['result'] == 0 && $rsp['errmsg'] == 'OK') {
                return true;
            } else {
                //记录错误信息
                $this->setError($rsp);
                return false;
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }
    }

    /**
     * 短信发送
     * @param Sms $params
     * @return mixed
     */
    private function baoSend($params)
    {
        $smsbao = new Smsbao($this->config);
        $result = $smsbao->mobile($params['mobile'])->msg("你的短信验证码是：{$params['code']}")->send();
        if(!$result){
            $this->setError($smsbao->getError());
        }
        return $result;
    }

    /**
     * 短信发送通知（msg参数直接构建实际短信内容即可）
     * @param   array $params
     * @return  boolean
     */
    private function baoNotice($params)
    {
        $smsbao = new Smsbao($this->config);
        $result = $smsbao->mobile($params['mobile'])->msg($params['msg'])->send();
        return $result;
    }


    /**
     * 记录失败信息
     * @param [type] $err [description]
     */
    private function setError($err)
    {
        $this->sendError = $err;
    }

    /**
     * 获取失败信息
     * @return [type] [description]
     */
    public function getError()
    {
        return $this->sendError;
    }

}
