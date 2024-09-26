<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\ResellerBind;
use addons\drama\model\ResellerLog;
use addons\drama\model\Richtext;
use EasyWeChat\Kernel\Exceptions\HttpException;
use fast\Http;
use think\Cache;
use think\Db;
use app\common\library\Sms;
use fast\Random;
use think\Log;
use think\Validate;
use addons\drama\library\Wechat;
use addons\drama\model\UserOauth;
use addons\drama\model\User as UserModel;
use addons\drama\model\Config;

/**
 * 会员管理
ALTER TABLE `82x9_com`.`gpt_user`
ADD COLUMN `usable` int(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'AI余额' AFTER `score`;
ALTER TABLE `82x9_com`.`gpt_user`
ADD COLUMN `vip_expiretime` bigint(20) NULL COMMENT 'VIP到期时间' AFTER `updatetime`;
 */
class User extends Base
{
    protected $noNeedLogin = ['saveVipInfo', 'qrcode', 'verify', 'wxOfficialAccountLogin', 'getWxPhone', 'wxMinilogin', 'accountLogin', 'smsLogin', 'smsRegister', 'register', 'forgotPwd', 'wxOfficialAccountOauth'];
    protected $noNeedRight = '*';

    public function _initialize()
    {
        return parent::_initialize();
    }

    /**
     * 会员中心
     */
    public function index()
    {
        $auth = \app\common\library\Auth::instance();
        $auth->setAllowFields(['id', 'parent_user_id', 'username', 'nickname', 'mobile', 'avatar', 'score', 'birthday', 'money',
            'group_id', 'verification', 'usable', 'vip_expiretime', 'reseller', 'is_vip','mgg']);
        $data = $auth->getUserinfo();
        $data['avatar'] = $data['avatar'] ? cdnurl($data['avatar'], true) : '';
        $data['vip_expiretime_text'] = $data['vip_expiretime'] ? date('Y-m-d', $data['vip_expiretime']) : '';
        $data['is_vip'] = $data['vip_expiretime'] > time() ? 1 : 0;

        $verification = $data['verification'];
        $verification->email = $verification->email ?? 0;
        $verification->mobile = $verification->mobile ?? 0;
        $verification->wxOfficialAccount = $verification->wxOfficialAccount ?? 0;
        $verification->wxMiniProgram = $verification->wxMiniProgram ?? 0;
        $data['verification'] = $verification;

        $user_oauth = UserOauth::where('user_id', $data['id'])->column('id', 'platform');
        $data['user_bind'] = $user_oauth;


        $data['reseller'] = ResellerBind::info();
        $data['parent_id'] = $data['parent_user_id'];
        $this->success('用户信息', $data);
    }


    /**
     * 分销商数据
     *
     * @return void
     */
    public function userData()
    {
        $auth = \app\common\library\Auth::instance();
        $auth->setAllowFields(['id', 'nickname', 'avatar', 'money', 'reseller']);
        $data = $auth->getUserinfo();
        $data['avatar'] = $data['avatar'] ? cdnurl($data['avatar'], true) : '';
        $data['reseller'] = ResellerBind::info();
        $data['reseller_money'] = ResellerLog::where('reseller_user_id', $data['id'])->sum('money');
        $data['today_reseller_money'] = ResellerLog::where('reseller_user_id', $data['id'])
            ->where('createtime', '>', strtotime(date('Y-m-d')))->sum('money');
        $config = Config::where('name', 'drama')->where('site_id', $this->site_id)->value('value');
        $config = json_decode($config, true);
        $data['reseller_desc'] = [];
        if(isset($config['reseller_desc']) && $config['reseller_desc']){
            $data['reseller_desc'] = Richtext::get($config['reseller_desc']);
        }
        $this->success('用户数据', $data);
    }

    /**
     * 密码登录
     * @ApiMethod   (POST)
     * @param string $account 账号
     * @param string $password 密码
     */
    public function accountLogin()
    {
        $account = $this->request->post('account');
        $password = $this->request->post('password');
        if (!$account || !$password) {
            $this->error(__('Invalid parameters'));
        }
        $ret = $this->auth->login($account, $password, $this->site_id);
        if ($ret) {
            $data = ['token' => $this->auth->getToken()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 短信登录注册
     * @ApiMethod   (POST)
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="code", type="string", required=true, description="验证码")
     * @ApiParams   (name="spm", type="string", required=false, description="分享标识")
     * @ApiParams   (name="platform", type="string", required=false, description="平台")
     * @ApiParams   (name="password", type="string", required=false, description="密码")
     */
    public function smsLogin()
    {
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        $spm = $this->request->post('spm', '');
        $platform = $this->request->post('platform', 'H5');
        $password = $this->request->post('password', '');
        if (!$mobile || !$code) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!Sms::check($mobile, $code, 'mobilelogin')) {
            $this->error(__('Captcha is incorrect'));
        }
        $user = \app\common\model\User::where('mobile', $mobile)->where('site_id', $this->site_id)->find();
        if ($user) {
            if ($user->status != 'normal') {
                $this->error(__('Account is locked'));
            }
            //如果已经有账号则直接登录
            $ret = $this->auth->direct($user->id);
        }else{
            $registerData['mobile'] = $mobile;
            $registerData['spm'] = $spm;
            $registerData['platform'] = $platform;
            $registerData['nickname'] = '';
            $registerData['avatar'] = '';
            $registerData['password'] = $password;
            $ret = $this->register_user($registerData);
        }
        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['token' => $this->auth->getToken()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 短信注册
     * @ApiMethod   (POST)
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="code", type="string", required=true, description="验证码")
     * @ApiParams   (name="spm", type="string", required=false, description="分享标识")
     * @ApiParams   (name="platform", type="string", required=false, description="平台")
     * @ApiParams   (name="password", type="string", required=false, description="密码")
     */
    public function smsRegister()
    {
        $mobile = $this->request->post('mobile');
        $code = $this->request->post('code');
        $password = $this->request->post('password', '');
        $spm = $this->request->post('spm', '');
        $platform = $this->request->post('platform', 'H5');
        if (!$mobile || !$code) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (!$password) {
            $this->error(__('请填写密码')); //TODO:密码规则校验
        }
        if (strlen($password) < 6 || strlen($password) > 16) {
            $this->error(__('密码长度 6-16 位')); //TODO:密码规则校验
        }
        if (!Sms::check($mobile, $code, 'register')) {
            $this->error(__('Captcha is incorrect'));
        }
        $registerData['mobile'] = $mobile;
        $registerData['spm'] = $spm;
        $registerData['platform'] = $platform;
        $registerData['nickname'] = '';
        $registerData['avatar'] = '';
        $registerData['password'] = $password;
        $ret = $this->register_user($registerData);

        if ($ret) {
            Sms::flush($mobile, 'mobilelogin');
            $data = ['token' => $this->auth->getToken()];
            $this->success(__('Logged in successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 授权绑定
     * @ApiMethod   (POST)
     * @ApiParams   (name="mobile", type="string", required=false, description="手机号")
     * @ApiParams   (name="code", type="string", required=false, description="验证码")
     * @ApiParams   (name="user_oauth_id", type="integer", required=false, description="授权id")
     * @ApiParams   (name="password", type="string", required=false, description="密码")
     * @ApiParams   (name="spm", type="string", required=false, description="分享标识")
     */
    public function register()
    {
        $mobile = $this->request->post('mobile', '');
        $password = $this->request->post('password', '');
        $code = $this->request->post('code', '');
        $user_oauth_id = $this->request->post('user_oauth_id', 0);
        $spm = $this->request->post('spm', '');
        if ($mobile && !Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('手机号填写错误'));
        }
        // 手机登录开关
        $configModel = new Config();
        $config = $configModel->where('site_id', $this->site_id)->where('name', 'drama')->value('value');
        $config = $config ? @json_decode($config, true) : [];
        $mobile_switch = isset($config['mobile_switch']) ? $config['mobile_switch'] : '0';
        // 授权用户
        $user_oauth = UserOauth::get($user_oauth_id);
        if(empty($user_oauth)){
            $this->error('授权失败,请授权后重试！');
        }
        // 强制手机号登录
        if($mobile_switch == '1'){
            // 手机号验证码
            if($code && $mobile){
                $ret = Sms::check($mobile, $code, 'changemobile');
                if (!$ret) {
                    $this->error(__('Captcha is incorrect'));
                }
            }else{// 小程序一键获取手机号
                if(empty($user_oauth['session_key'])){
                    $this->error('手机号验证失败，请重启应用');
                }
                $mobile = $user_oauth['session_key'];
                $user_oauth->session_key = '';
                $user_oauth->save();
            }
            $user = \app\common\model\User::where('mobile', $mobile)->where('site_id', $this->site_id)->find();
            if(empty($user)){
                if($this->auth->isLogin()){
                    $ret = $this->auth->getUser()->save(['mobile'=>$mobile]);
                    if($ret){
                        if(!empty($mobile)) {
                            $user1 = $this->auth->getUser();
                            $verification = $user1->verification;
                            $verification->mobile = 1;
                            $user1->verification = $verification;
                            $user1->save();
                        }
                    }
                }else{
                    $registerData['mobile'] = $mobile;
                    $registerData['password'] = $password;
                    $registerData['spm'] = $spm;
                    $registerData['platform'] = $user_oauth['platform'];
                    $registerData['nickname'] = $user_oauth['nickname'] ?? '';
                    $registerData['avatar'] = $user_oauth['headimgurl'] ?? '';
                    $ret = $this->register_user($registerData);
                }
            }else{
                if ($user_oauth['user_id'] != 0 && $user_oauth['user_id'] != $user->id && UserModel::get($user_oauth['user_id'])) {
                    $this->error('该手机号已被其他用户绑定,请更换手机号绑定或注销当前账号重新登录！', null, 1314);
                }
                //如果已经有账号则直接登录
                $ret = $this->auth->direct($user->id);
            }
        }else{
            if($code && $mobile){
                $ret = Sms::check($mobile, $code, 'changemobile');
                if (!$ret) {
                    $this->error(__('Captcha is incorrect'));
                }
            }
            $registerData['mobile'] = $mobile;
            $registerData['password'] = $password;
            $registerData['spm'] = $spm;
            $registerData['platform'] = $user_oauth['platform'];
            $registerData['nickname'] = $user_oauth['nickname'] ?? '';
            $registerData['avatar'] = $user_oauth['headimgurl'] ?? '';
            $ret = $this->register_user($registerData);
        }
        if($ret){
            $user_oauth->user_id = $this->auth->getUser()->id;
            $user_oauth->save();
            $this->setUserVerification($this->auth->getUser(), $user_oauth['provider'], $user_oauth['platform']);
            $data = ['token' => $this->auth->getToken()];
            $this->success(__('绑定成功'), $data);
        }else{
            $this->success('绑定失败：'.$this->auth->getError());
        }
    }

    private function register_user($registerData){
        $username = Random::alnum(20);
        $password = $registerData['password'] ?? '';
        $mobile = $registerData['mobile'] ?? '';
        $domain = request()->host();
        $extend = $this->getUserDefaultFields();
        $extend['user_head'] = $this->match;
        $extend['nickname'] = $registerData['nickname'] ? $registerData['nickname'] : $extend['nickname'];
        $extend['avatar'] = $registerData['avatar'] ? $registerData['avatar'] : $extend['avatar'];
        $extend['site_id'] = $this->site_id;
        $registerResult = $this->auth->register($username, $password, $username . '@' . $domain, $mobile, $extend);
        if(!$registerResult) {
            $this->error($this->auth->getError());
        }else{
            //$this->{$extend['user_head']}();
        }
        $user = $this->auth->getUser();
        $user_id = $user['id'];
        if (empty($registerData['nickname'])) {
            $user->nickname = $extend['nickname'] . $this->auth->getUser()->id;
            $user->save();
        }
        if(!empty($user->mobile)) {
            $verification = $user->verification;
            $verification->mobile = 1;
            $user->verification = $verification;
            $user->save();
        }
        try {
            \think\Hook::listen('user_register_after', $user_id);
            if(isset($registerData['spm']) && $registerData['spm']){
                $share = ['spm'=>$registerData['spm'], 'platform'=>$registerData['platform']];
                \think\Hook::listen('register_after', $share);
            }
        }catch (\Exception $e){
            Log::error('User-Reseller'.$e->getMessage());
        }
        return true;
    }

    /**
     * 忘记密码
     * @ApiMethod   (POST)
     * @param string $mobile 手机号
     * @param string $password 新密码
     * @param string $code 验证码
     */
    public function forgotPwd()
    {
        $mobile = $this->request->post("mobile");
        $newpassword = $this->request->post("password");
        $captcha = $this->request->post("code");
        if (!$newpassword || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (strlen($newpassword) < 6 || strlen($newpassword) > 16) {
            $this->error(__('密码长度 6-16 位')); //TODO:密码规则校验
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        $user = \app\common\model\User::where('mobile', $mobile)->where('site_id', $this->site_id)->find();
        if (!$user) {
            $this->error(__('User not found'));
        }
        $ret = Sms::check($mobile, $captcha, 'resetpwd');
        if (!$ret) {
            $this->error(__('Captcha is incorrect'));
        }
        Sms::flush($mobile, 'resetpwd');
        //模拟一次登录
        $this->auth->direct($user->id);
        $ret = $this->auth->changepwd($newpassword, '', true);
        if ($ret) {
            $this->success(__('Reset password successful'));
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 登录用户绑定手机号
     * @ApiInternal
     * @ApiMethod   (POST)
     * @ApiParams   (name="mobile", type="string", required=true, description="手机号")
     * @ApiParams   (name="code", type="string", required=true, description="验证码")
     */
    public function bindMobile()
    {
        $user = $this->auth->getUser();
        $mobile = $this->request->post('mobile');
        $captcha = $this->request->post('code');
        if (!$mobile || !$captcha) {
            $this->error(__('Invalid parameters'));
        }
        if (!Validate::regex($mobile, "^1\d{10}$")) {
            $this->error(__('Mobile is incorrect'));
        }
        if (\app\common\model\User::where('mobile', $mobile)->where('id', '<>', $user->id)->find()) {
            $this->error(__('Mobile already exist'));
        }
        $result = Sms::check($mobile, $captcha, 'changemobile');
        if (!$result) {
            $this->error(__('Captcha is incorrect'));
        }
        $verification = $user->verification;
        $verification->mobile = 1;
        $user->verification = $verification;
        $user->mobile = $mobile;
        $user->save();

        Sms::flush($mobile, 'changemobile');
        $this->success(__('Mobile is binded'));
    }

    /**
     * 修改密码
     * @ApiMethod   (POST)
     * @param string $oldpassword 手机号
     * @param string $newpassword 验证码
     */
    public function changePwd()
    {
        $user = $this->auth->getUser();

        $oldpassword = $this->request->post("oldpassword");
        $newpassword = $this->request->post("newpassword");

        if (!$newpassword || !$oldpassword) {
            $this->error(__('Invalid parameters'));
        }
        if (strlen($newpassword) < 6 || strlen($newpassword) > 16) {
            $this->error(__('密码长度 6-16 位')); //TODO:密码规则校验
        }

        $ret = $this->auth->changepwd($newpassword, $oldpassword);

        if ($ret) {
            $this->auth->direct($user->id);
            $data = ['userinfo' => $this->auth->getUserinfo()];

            $this->success(__('Change password successful'), $data);
        } else {
            $this->error($this->auth->getError());
        }
    }

    /**
     * 微信公众号登录(回调地址)
     * @ApiSummary  (event：login登录注册，refresh更新微信头像昵称，bind手机号登录绑定公众号，token：event为refresh或bind的时候必传)
     * @param string $code 加密code
     */
    public function wxOfficialAccountOauth()
    {
        $params = $this->request->get();
        $payload = json_decode(htmlspecialchars_decode($params['payload']), true);
        // 解析前端主机
        if ($payload['event'] !== 'login' && $payload['token'] !== '') {
            $this->auth->init($payload['token']);
        }
        try {
            $wechat = new Wechat('wxOfficialAccount');
            $oauth = $wechat->oauth();
            $decryptData = $oauth->user()->getOriginal();
        }catch (HttpException $e){
            $this->error('公众号配置错误：'.$e->getMessage());
        }
        $result = Db::transaction(function () use ($payload, $decryptData) {
            try {
                $data = $this->oauthLoginOrRegisterOrBindOrRefresh($payload['event'], $decryptData, 'wxOfficialAccount', 'Wechat');
                return $data;
            } catch (\Exception $e) {
                $token = $payload['token'] ?? '';
                header('Location: ' . trim($payload['host'], '/')  . '?error=' . $e->getMessage().'&user_oauth_id=&token='.$token);
            }
        });
        if($result){
            if(isset($result['token'])){
                header('Location: ' . trim($payload['host'], '/')  . '?token=' . $result['token'].'&user_oauth_id=');
            }else{
                header('Location: ' .  trim($payload['host'], '/')  . '?user_oauth_id='.$result['user_oauth_id'].'&token=');
            }
        }else{
            header('Location: ' .  trim($payload['host'], '/')  . '?token=&user_oauth_id=');
        }
        exit;
    }

    /**
     * 扫码登陆
     * @ApiInternal
     */
    public function wxOfficialAccountLogin()
    {
        $key = $this->request->get('key', '');
        // if(!Cache::has($key)){
        //     exit;
        // }
        try {
            $wechat = new Wechat('wxOfficialAccount');
            $oauth = $wechat->oauth();
            $decryptData = $oauth->user()->getOriginal();
        }catch (HttpException $e){
            $this->error('公众号配置错误：'.$e->getMessage());
        }

        $time = @json_decode(Cache::get($key), true)['time'];
        $openid = @json_decode(Cache::get($key), true)['openid'];
        $expire = $time - time();
        $expire = $expire < 60 ? 60 : $expire;
        $key_data = [
            'sao'=>1,
            'auth'=>1,
            'register'=>0,
            'openid'=>$openid,
            'data'=>$decryptData,
            'time'=>$time
        ];
        Cache::set($key, json_encode($key_data), $expire);

        header('Location: ' . request()->domain().'/oauth.html');
        exit;
    }

    /**
     * 获取微信二维码
     */
    public function qrcode(){
        //设置标识
        $key = Random::alnum(20);
        try {
            $wechat = new Wechat('wxOfficialAccount', $this->sign);
            $data = $wechat->getAccessToken();
            $access_token = $data['access_token'];
        }catch (HttpException $e){
            $this->error('公众号授权信息错误：'.$e->getMessage());
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        $data = '{"expire_seconds": 60, "action_name": "QR_STR_SCENE", "action_info": {"scene": {"scene_str": "'.$key.'"}}}';
        $result = Http::sendRequest($url, $data, 'POST');
        $result = json_decode($result['msg'], true);
        $result['url'] = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($result['ticket']);
        if(isset($result['errcode'])){
            $this->error($result['errmsg'], null, $result['errcode']);
        }
        $result['key'] = $key;
        $key_data = [
            'sao'=>0, # 是否扫码
            'auth'=>0, # 是否授权
            'register'=>0, # 是否注册
            'openid'=>'', # 微信标识
            'data'=>[],
            'time'=>time()+120 # 过期时间
        ];
        Cache::set($key, json_encode($key_data), 120);
        $this->success('', $result);
    }

    /**
     * 扫码登陆验证
     * @ApiParams   (name="key", type="string", required=true, description="key")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function verify(){
        $key = $this->request->get('key', '');
        if($key == ''){
            $this->error('未扫码', null, 101);
        }
        if(!Cache::has($key)){
            $this->error('已过期', null, 103);
        }
        $data = json_decode(Cache::get($key), true);
        if($data['sao'] == 0){
            $this->error('未扫码', null, 101);
        }else{
            if($data['auth'] == 0){
                $this->error('已扫码，未授权', null, 102);
            }else{
                $decryptData = $data['data'];
                try {
                    $result = $this->oauthLoginOrRegisterOrBindOrRefresh('login', $decryptData, 'wxOfficialAccount', 'Wechat');
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }
                if($result){
                    if(isset($result['token'])){
                        $userOauth = UserOauth::where(['openid' => $decryptData['openid'], 'site_id' => $this->site_id,
                            'user_id' => $this->auth->getUser()->id])->find();
                        if (!empty($decryptData['nickname'])) {
                            $refreshFields['nickname'] = $decryptData['nickname'];
                        }
                        if (!empty($decryptData['headimgurl'])) {
                            $refreshFields['avatar'] = $decryptData['headimgurl'];
                        }
                        $this->auth->getUser()->save($refreshFields);
                        $userOauth->allowField(true)->save($decryptData);
                        $this->success('已授权', ['token'=>$result['token']], 100);
                    }else{
                        $this->success('授权成功', ['user_oauth_id' => $result['user_oauth_id']], 99);
                    }
                }
            }
        }
    }

    /**
     * 获取手机号码
     * @ApiMethod (POST)
     * @ApiParams   (name="code", type="string", required=true, description="手机号获取凭证")
     * @ApiParams   (name="user_oauth_id", type="integer", required=false, description="授权id")
     */
    public function getWxPhone(){
        $code = $this->request->post('code');
        $user_oauth_id = $this->request->post('user_oauth_id');

        $user_oauth = UserOauth::get($user_oauth_id);
        if(empty($user_oauth)){
            $this->error('请先授权后再获取手机号');
        }
        try {
            $wechat = new Wechat('wxMiniProgram');
            $tmptoken = $wechat->access_token->getToken();
        }catch (HttpException $e){
            $this->error('小程序配置错误：'.$e->getMessage());
        }
        if(isset($tmptoken['access_token'])){
            $token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
        }

        $url = "https://api.weixin.qq.com/wxa/business/getuserphonenumber?access_token=".$token;

        $data['code']=$code;

        $info = Http::sendRequest($url, json_encode($data), 'POST');
        $tmpinfo = json_decode($info['msg'],true);

        $state = $tmpinfo['errcode'];

        if($state == '0'){
            $phoneNumber = $tmpinfo['phone_info']['phoneNumber'];
            $user_oauth->session_key = $phoneNumber;
            $user_oauth->save();
            $this->success('获取手机号成功');
        }else{
            $this->error($tmpinfo['errmsg']);
        }
    }

    /**
     * 微信小程序登录
     * @ApiMethod (POST)
     * @ApiParams   (name="code", type="string", required=true, description="js_code")
     * @ApiParams   (name="event", type="string", required=false, description="login登录注册，bind手机号登录后绑定")
     */
    public function wxMinilogin()
    {
        $code = $this->request->param("code");
        $event = $this->request->param("event", 'login');
        if (!$code || !in_array($event, ['login', 'bind'])) {
            $this->error("参数不正确");
        }
        try {
            $wechat = new Wechat('wxMiniProgram');
            $json = $wechat->getApp()->auth->session($code);
        }catch (HttpException $e){
            $this->error('小程序配置错误：'.$e->getMessage());
        }
        if (isset($json['openid'])) {
            $decryptData = [
                'openid'        => $json['openid'],
                'unionid'       => $json['unionid'] ?? '',
                'mobile'        => '',
                'access_token'  => $json['session_key'],
                'expires_in'    => isset($json['expires_in']) ? $json['expires_in'] : 0,
            ];

            try {
                $result = $this->oauthLoginOrRegisterOrBindOrRefresh($event, $decryptData, 'wxMiniProgram', 'Wechat');
            } catch (\Exception $e) {
                $this->error($e->getMessage());
            }
            if($result){
                if(isset($result['token'])){
                    $this->success("登录成功", ['token' => $result['token']]);
                }else{
                    $this->success('授权成功', ['user_oauth_id' => $result['user_oauth_id']]);
                }
            }else{
                $this->error("登录失败");
            }
        } else {
            $this->error("登录失败:无法获取openID，".$json['errmsg']);
        }
    }

    /**
     * 第三方登录或自动注册或绑定
     * @ApiInternal
     * @param string  $event        事件:login=登录, refresh=更新账号授权信息, bind=绑定第三方授权
     * @param array   $decryptData  解密参数
     * @param string  $platform     平台名称
     * @param string  $provider     厂商名称
     * @return string $token        返回用户token
     */
    private function oauthLoginOrRegisterOrBindOrRefresh($event, $decryptData, $platform, $provider)
    {
        $oauthData = $decryptData;
        $oauthData = array_merge($oauthData, [
            'provider' => $provider,
            'platform' => $platform,

        ]);
        if ($platform === 'wxMiniProgram' || $platform === 'App') {
            $oauthData['expire_in'] = 7200;
            $oauthData['expiretime'] = time() + 7200;
        }
        if (!$decryptData['openid']) {
            throw new \Exception('未找到第三方授权ID');
        }
        $userOauth = UserOauth::where(['openid' => $decryptData['openid'], 'site_id' => $this->site_id])->where('platform', $platform)->where('provider', $provider)->lock(true)->find();
        switch ($event) {
            case 'login':               // 登录(自动注册)
                if (!$userOauth) {      // 没有找到第三方登录信息 创建新用户
                    $oauthData['logintime'] = time();
                    $oauthData['logincount'] = 1;
                    // 判断是否有unionid 并且已存在oauth数据中
                    if (isset($oauthData['unionid']) && $oauthData['unionid']) {
                        //存在同厂商信息，添加oauthData数据，合并用户
                        $userUnionOauth = UserOauth::get(['site_id' => $this->site_id, 'unionid' => $oauthData['unionid'], 'provider' => $provider]);
                        if ($userUnionOauth) {
                            if($userUnionOauth['user_id']){
                                $this->auth->direct($userUnionOauth->user_id);
                            }else{
                                return ['user_oauth_id'=>$userUnionOauth['id']];
                            }
                        }
                    }

                    $oauthData['user_id'] = 0;
                    $oauthData['createtime'] = time();
                    $oauthData['site_id'] = $this->site_id;
                    $user_oauth_id = UserOauth::strict(false)->insertGetId($oauthData);
                    return ['user_oauth_id'=>$user_oauth_id];
                } else {
                    // 找到第三方登录信息，直接登录
                    $user_id = $userOauth->user_id;
                    if ($user_id && $this->auth->direct($user_id) && $this->auth->getUser()) {       // 获取到用户
                        $oauthData['logincount'] = $userOauth->logincount + 1;
                        $oauthData['logintime'] = time();
                        $userOauth->allowField(true)->save($oauthData);
                    } else {         // 用户已被删除 重新执行登录
                        return ['user_oauth_id'=>$userOauth['id']];
                    }
                }
                break;
            case 'refresh':
                if (!$userOauth) {
                    throw new \Exception('未找到第三方授权账户');
                }
                if (!empty($oauthData['nickname'])) {
                    $refreshFields['nickname'] = $oauthData['nickname'];
                }
                if (!empty($oauthData['headimgurl'])) {
                    $refreshFields['avatar'] = $oauthData['headimgurl'];
                }
                $this->auth->getUser()->save($refreshFields);
                $userOauth->allowField(true)->save($oauthData);
                break;
            case 'bind':
                if (!$this->auth->getUser()) {
                    throw new \Exception('请先登录');
                }

                $oauthData['user_id'] = $this->auth->getUser()->id;

                if ($userOauth) {
                    if ($userOauth['user_id'] != 0 && $userOauth['user_id'] != $this->auth->getUser()->id && UserModel::get($userOauth['user_id'])) {
                        throw new \Exception('该账号已被其他用户绑定');
                    }
                    $oauthData['id'] = $userOauth->id;
                    $userOauth->strict(false)->update($oauthData);
                } else {
                    $oauthData['logincount'] = 1;
                    $oauthData['logintime'] = time();
                    $oauthData['createtime'] = time();
                    $oauthData['site_id'] = $this->site_id;
                    UserOauth::strict(false)->insert($oauthData);
                }
                break;
        }
        if ($this->auth->getUser()) {
            $this->setUserVerification($this->auth->getUser(), $provider, $platform);
            return ['token'=>$this->auth->getToken()];
        }
        return false;
    }

    /**
     * 第三方用户授权信息
     * @ApiParams   (name="platform", type="string", required=true, description="平台")
     */
    public function thirdOauthInfo()
    {
        $user = $this->auth->getUser();
        $platform = $this->request->get('platform');
        $userOauth = UserOauth::where([
            'platform' => $platform,
            'user_id'  => $user->id
        ])->field('headimgurl, nickname')->find();
        $this->success('获取成功', $userOauth);
    }

    /**
     * 解除绑定
     * @ApiMethod   (POST)
     * @ApiParams   (name="platform", type="string", required=true, description="平台")
     * @ApiParams   (name="provider", type="string", required=true, description="厂商：Wechat微信")
     */
    public function unbindThirdOauth()
    {
        $user = $this->auth->getUser();
        $platform = $this->request->post('platform');
        $provider = $this->request->post('provider');

        $verification = $user->verification;
        if (!$verification->mobile) {
            $this->error('请先绑定手机号再进行解绑操作');
        }

        $verifyField = $platform;
        if ($platform === 'App' && $provider === 'Wechat') {
            $verifyField = 'wxOpenPlatform';
        }

        $verification->$verifyField = 0;
        $user->verification = $verification;
        $user->save();
        $userOauth = UserOauth::where([
            'platform' => $platform,
            'provider'  => $provider,
            'user_id' => $user->id
        ])->delete();
        if ($userOauth) {
            $this->success('解绑成功');
        }
        $this->error('解绑失败');
    }

    /**
     * 注销登录
     */
    public function logout()
    {
        if ($this->auth->isLogin()) {
            $this->auth->logout();
        }
        $this->success(__('Logout successful'));
    }

    /**
     * 修改会员个人信息
     * @ApiMethod (POST)
     * @param string $avatar 头像地址
     * @param string $username 用户名
     * @param string $nickname 昵称
     * @param string $birthday 生日
     * @param string $bio 个人简介
     */
    public function profile()
    {
        $user = $this->auth->getUser();
        $user_id = $user['id'];
        $username = $this->request->post('username');
        $nickname = $this->request->post('nickname');
        $bio = $this->request->post('bio', '');
        $birthday = $this->request->post('birthday', '');
        $avatar = $this->request->post('avatar', '', 'trim,strip_tags,htmlspecialchars');
        if ($username) {
            $exists = \app\common\model\User::where('username', $username)->where('site_id', $this->site_id)
                ->where('id', '<>', $this->auth->id)->find();
            if ($exists) {
                $this->error(__('Username already exists'));
            }
            $user->username = $username;
        }
        if(!empty($nickname)){
            $user->nickname = $nickname;
            try {
                \think\Hook::listen('user_bind_name_after', $user_id);
            }catch (\Exception $e){}
        }
        if(!empty($bio)){
            $user->bio = $bio;
        }
        if(!empty($birthday)){
            $user->birthday = $birthday;
        }
        if (!empty($avatar)) {
            $user->avatar = $avatar;
            try {
                \think\Hook::listen('user_bind_avatar_after', $user_id);
            }catch (\Exception $e){}
        }
        $user->save();
        $this->success();
    }

    /**
     * 用户注销
     *
     * @return void
     */
    public function delete()
    {
        $user = $this->auth->getUser();
        $this->auth->delete($user->id);

        UserOauth::where('user_id', $user->id)->delete();

        $this->success('注销成功');
    }

    private function getUserDefaultFields()
    {   $userConfig = Config::get(['name' => 'user', 'site_id'=>$this->site_id]);
        $userConfig = isset($userConfig->value) ? json_decode($userConfig->value, true) : ['nickname'=>'Mett -', 'avatar'=>'/assets/img/logo.png'];
        return $userConfig;
    }

    private function setUserVerification($user, $provider, $platform)
    {
        $verification = $user->verification;
        if ($platform === 'App') {
            $platform = '';
            if ($provider === 'Wechat') {
                $platform = 'wxOpenPlatform';
            } elseif ($provider === 'Alipay') {
                $platform = 'aliOpenPlatform';
            }
        }
        if ($platform !== '') {
            $verification->$platform = 1;
            $user->verification = $verification;
            $user->save();
        }
    }


    public function saveVipInfo(){
        $mobile = $this->request->param('mobile');
        $vip_time = $this->request->param('vip_time');
        if(empty($mobile) || empty($vip_time)){
            $this->error('参数错误！');
        }
        $vip_time = strtotime(date('Y-m-d')) + 86400 * $vip_time;
        $filename = ROOT_PATH . 'addons'.DS.'drama'.DS.'library'.DS.'data'.DS.'vip.json';
        $path = dirname($filename);
        if(!is_dir($path)){
            mkdir($path, 0755, true);
        }
        if(!is_file($filename)){
            $fp = fopen($filename, 'w'); // 打开文件，以追加写入的方式
            if($fp){
                fclose($fp);
            }else{
                $this->error('权限不足，无法创建文件！');
            }
        }
        $fp = fopen($filename, 'r+'); // 打开文件，以追加写入的方式
        if (flock($fp, LOCK_EX)) { // 获取独占锁
            // 读取内容
            try{
                $content = fread($fp, filesize($filename));
                $data = @json_decode($content, true);
            }catch (\Exception $e){}
            $data[$mobile] = $vip_time;
            // 定位到文件开头
            rewind($fp);
            fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE)); // 写入文件
            flock($fp, LOCK_UN); // 释放锁
        } else {
            $this->error('无法获取文件锁！');
        }
        fclose($fp); // 关闭文件句柄
    }
}
