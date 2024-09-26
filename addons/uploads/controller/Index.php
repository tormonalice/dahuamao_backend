<?php

namespace addons\uploads\controller;

use think\addons\Controller;
use think\Config;

/**
 * 云储存
 *
 */
class Index extends Controller
{
    public function index()
    {
        Config::set('default_return_type', 'html');
        $this->error("当前插件暂无前台页面");
    }

}

