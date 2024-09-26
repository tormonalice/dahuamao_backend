<?php


if (!function_exists('get_site_id')) {
    function get_site_id()
    {
        $site_id = 0;
        // 通过站点标识参数获取
        $sign = request()->header('sign', '');
        if($sign == ''){
            $sign = request()->param('sign', '');
        }
        $sign = substr(trim($sign), 0, 4);
        if($sign){
            $site_info = \think\Db::name('sites')->where('sign', $sign)->find();
            if($site_info){
                $site_id = $site_info['site_id'];
            }
        }

        // 通过登录用户获取
        if(!$site_id){
            $module = request()->module();
            $module = $module ? strtolower($module) : 'index';
            if(($module == 'admin' && \app\admin\library\Auth::instance()->id)){
                $site_id = \app\admin\library\Auth::instance()->id;
            }elseif($module != 'admin' && \app\common\library\Auth::instance()->id){
                if(isset(\app\common\library\Auth::instance()->getUser()->site_id)){
                    $site_id = \app\common\library\Auth::instance()->getUser()->site_id;
                }
            }else{
                $site_id = \app\admin\library\Auth::instance()->id;
                if(!$site_id){
                    if(isset(\app\common\library\Auth::instance()->getUser()->site_id)){
                        $site_id = \app\common\library\Auth::instance()->getUser()->site_id;
                    }
                }
            }
        }

        // 未登录获取站点ID
        if(!$site_id){
            $domain = $_SERVER['HTTP_HOST'];
            $site_info = \think\Db::name('sites')->where('domain', $domain)->where('status', 'normal')->find();
            if($site_info){
                $sign = $site_info['sign'] ?? '';
            }else{
                $site_info = \think\Db::name('sites')->where('is_default', 1)->where('status', 'normal')->find();
                $sign = $site_info['sign'] ?? '';
            }

            if($sign){
                $site_info = \think\Db::name('sites')->where('sign', $sign)->find();
                if($site_info){
                    $site_id = $site_info['site_id'];
                }
            }

        }

        return $site_id;
    }
}

if (!function_exists('get_uploads_config')) {
    function get_uploads_config($site_id, $type=null)
    {
        $addon_list = get_addon_list();
        $table_name = '';
        if(isset($addon_list['chatgpt'])){
            $table_name = 'chatgpt_config';
        }elseif(isset($addon_list['drama'])){
            $table_name = 'drama_config';
        }
        $config = \think\Db::name($table_name)
            ->where('site_id', $site_id)
            ->where('name', 'uploads')
            ->find();
        $config = $config ? json_decode($config['value'], true) : [];
        if($type === null){
            $type = $config['upload_type'] ?? '';
        }
        if(isset($config[$type])){
            $data = $config[$type] ?? [];
            $data['upload_type'] = $type;
        }
        return $data ?? [] ;
    }
}



