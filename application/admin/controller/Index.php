<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\AdminLog;
use app\common\controller\Backend;
use think\Config;
use think\Hook;
use think\Session;
use think\Validate;

/**
 * 后台首页
 * @internal
 */
class Index extends Backend
{

    protected $noNeedLogin = ['login'];
    protected $noNeedRight = ['index', 'logout'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
        //移除HTML标签
        $this->request->filter('trim,strip_tags,htmlspecialchars');
    }

    /**
     * 后台首页
     */
    public function index()
    {
        $cookieArr = ['adminskin' => "/^skin\-([a-z\-]+)\$/i", 'multiplenav' => "/^(0|1)\$/", 'multipletab' => "/^(0|1)\$/", 'show_submenu' => "/^(0|1)\$/"];
        foreach ($cookieArr as $key => $regex) {
            $cookieValue = $this->request->cookie($key);
            if (!is_null($cookieValue) && preg_match($regex, $cookieValue)) {
                config('fastadmin.' . $key, $cookieValue);
            }
        }
        //左侧菜单
        list($menulist, $navlist, $fixedmenu, $referermenu) = $this->auth->getSidebar([
            'dashboard' => 'hot',
            'addon'     => ['new', 'red', 'badge'],
            'auth/rule' => __('Menu'),
        ], $this->view->site['fixedpage']);
        $action = $this->request->request('action');
        if ($this->request->isPost()) {
            if ($action == 'refreshmenu') {
                $this->success('', null, ['menulist' => $menulist, 'navlist' => $navlist]);
            }
        }
        $this->assignconfig('cookie', ['prefix' => config('cookie.prefix')]);
        $this->view->assign('menulist', $menulist);
        $this->view->assign('navlist', $navlist);
        $this->view->assign('fixedmenu', $fixedmenu);
        $this->view->assign('referermenu', $referermenu);
        $this->view->assign('title', __('Home'));
        return $this->view->fetch();
    }

    /**
     * 管理员登录
     */
    public function login()
    {
        $url = $this->request->get('url', '', 'url_clean');
        $url = $url ?: 'index/index';
        if ($this->auth->isLogin()) {
            $this->success(__("You've logged in, do not login again"), $url);
        }
        //保持会话有效时长，单位:小时
        $keeyloginhours = 24;
        if ($this->request->isPost()) {
            $username = $this->request->post('username');
            $password = $this->request->post('password', '', null);
            $keeplogin = $this->request->post('keeplogin');
            $token = $this->request->post('__token__');
            $rule = [
                'username'  => 'require|length:3,30',
                'password'  => 'require|length:3,30',
                '__token__' => 'require|token',
            ];
            $data = [
                'username'  => $username,
                'password'  => $password,
                '__token__' => $token,
            ];
            if (Config::get('fastadmin.login_captcha')) {
                $rule['captcha'] = 'require|captcha';
                $data['captcha'] = $this->request->post('captcha');
            }
            $validate = new Validate($rule, [], ['username' => __('Username'), 'password' => __('Password'), 'captcha' => __('Captcha')]);
            $result = $validate->check($data);
            if (!$result) {
                $this->error($validate->getError(), $url, ['token' => $this->request->token()]);
            }
            AdminLog::setTitle(__('Login'));

            $admin = Admin::get(['username' => $username]);
            if($admin){
                $site_info = \app\admin\model\Sites::get($admin['id']);
                if($site_info && $site_info['domain'] && $site_info['domain'] != $_SERVER['HTTP_HOST']){
                    $this->error('请使用绑定的域名登录：'.$site_info['domain'], $url, ['token' => $this->request->token()]);
                }
            }
            $result = $this->auth->login($username, $password, $keeplogin ? $keeyloginhours * 3600 : 0);
            if ($result === true) {
                Hook::listen("admin_login_after", $this->request);
                $this->success(__('Login successful'), $url, ['url' => $url, 'id' => $this->auth->id, 'username' => $username, 'avatar' => $this->auth->avatar]);
            } else {
                $msg = $this->auth->getError();
                $msg = $msg ? $msg : __('Username or password is incorrect');
                $this->error($msg, $url, ['token' => $this->request->token()]);
            }
        }

        // 根据客户端的cookie,判断是否可以自动登录
        if ($this->auth->autologin()) {
            Session::delete("referer");
            $this->redirect($url);
        }

        $site_info = \app\admin\model\Sites::where('domain', $_SERVER['HTTP_HOST'])->find();
        if(empty($site_info)){
            $site_info = \app\admin\model\Sites::where('is_default', 1)->find();
        }
        $site_id = $site_info['site_id'] ?? 0;
        $config = \addons\drama\model\Config::where('site_id', $site_id)->where('name', 'drama')->find();
        $config = $config ? json_decode($config['value'], true) : [];
        $title = isset($config['name']) && $config['name'] ? $config['name'] : '短剧';
        $logo = isset($config['logo']) && $config['logo'] ? cdnurl($config['logo']) : '/assets/img/logo.png';
        $copyrights = $config['copyright']['list'] ?? [];
        $copyright = '';
        foreach ($copyrights as $item){
            $copyright .= $item['name'].'<br />';
        }
        $this->view->assign('title', $title);
        $this->view->assign('logo', $logo);
        $this->view->assign('copyright', $copyright);
        $this->view->assign('keeyloginhours', $keeyloginhours);
        Hook::listen("admin_login_init", $this->request);
        return $this->view->fetch();
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        if ($this->request->isPost()) {
            $this->auth->logout();
            Hook::listen("admin_logout_after", $this->request);
            $this->success(__('Logout successful'), 'index/login');
        }
        $html = "<form id='logout_submit' name='logout_submit' action='' method='post'>" . token() . "<input type='submit' value='ok' style='display:none;'></form>";
        $html .= "<script>document.forms['logout_submit'].submit();</script>";

        return $html;
    }

}
