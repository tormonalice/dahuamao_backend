<?php

namespace addons\drama\controller;

use app\common\library\Sms as Smslib;
use addons\drama\model\User;
use think\Hook;

/**
 * 手机短信接口
 */
class Sms extends Base
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';

    /**
     * 发送验证码
     * @ApiMethod   (POST)
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="event", type="string", required=true, description="事件：changemobile修改绑定register注册changepwd修改密码resetpwd忘记密码mobilelogin手机登录")
     */
    public function send()
    {
        $mobile = $this->request->post("mobile");
        $event = $this->request->post("event");
        $event = $event ? $event : 'register';

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        $last = Smslib::get($mobile, $event);
        if ($last && time() - $last['createtime'] < 60) {
            $this->error(__('发送频繁'));
        }
        $ipSendTotal = \app\common\model\Sms::where(['ip' => $this->request->ip()])->whereTime('createtime', '-1 hours')->count();
        if ($ipSendTotal >= 10) {
            $this->error(__('发送频繁'));
        }
        if ($event) {
            $userinfo = User::where('mobile', $mobile)->where('site_id', $this->site_id)->find();
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            // } elseif (in_array($event, ['changemobile']) && $userinfo) {
            //     //被占用
            //     $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::send($this->site_id, $mobile, null, $event);
        if ($ret === true) {
            $this->success(__('发送成功'));
        } else {
            $this->error(__('发送失败，请检查短信配置是否正确:'.$ret));
        }
    }

    /**
     * 检测验证码
     * @ApiInternal
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="captcha", type="string", required=true, description="验证码")
     * @ApiParams   (name="event", type="string", required=true, description="事件：changemobile修改绑定register注册changepwd修改密码resetpwd重置密码mobilelogin手机登录")
     */
    public function check()
    {
        $mobile = $this->request->post("mobile");
        $event = $this->request->post("event");
        $event = $event ? $event : 'register';
        $captcha = $this->request->post("captcha");

        if (!$mobile || !\think\Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号不正确'));
        }
        if ($event) {
            $userinfo = User::getByMobile($mobile);
            if ($event == 'register' && $userinfo) {
                //已被注册
                $this->error(__('已被注册'));
            } elseif (in_array($event, ['changemobile']) && $userinfo) {
                //被占用
                $this->error(__('已被占用'));
            } elseif (in_array($event, ['changepwd', 'resetpwd', 'mobilelogin']) && !$userinfo) {
                //未注册
                $this->error(__('未注册'));
            }
        }
        $ret = Smslib::check($mobile, $captcha, $event);
        if ($ret) {
            $this->success(__('成功'));
        } else {
            $this->error(__('验证码不正确'));
        }
    }
}
