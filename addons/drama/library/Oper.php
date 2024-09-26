<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\library;

use app\admin\library\Auth as AdminAuth;
use addons\drama\model\User;

class Oper
{
    public static function set($operType = '', $operId = 0)
    {
        if ($operType === '') {
            // 自动获取操作人
            if (strpos(request()->url(), 'addons/drama') !== false) {
                // 用户
                $user = User::info();
                if ($user) {
                    $operType = 'user';
                    $operId = $user->id;
                }
            }else{
                $admin = AdminAuth::instance();     // 没有登录返回的还是这个类实例
                if ($admin->isLogin()) {
                    // 后台管理员
                    $operType = 'admin';
                    $operId = $admin->id;
                }
            }
        }
        if ($operType === '') {
            $operType = 'system';
        }
        return [
            'oper_type' => $operType,
            'oper_id' => $operId
        ];
    }

    public static function get($operType, $operId)
    {
        $operator = null;
        if ($operType === 'admin') {
            $operator = \app\admin\model\Admin::where('id', $operId)->field('nickname as name, avatar')->find();
            $operator['type'] = '管理员';
        } elseif ($operType === 'user') {
            $operator = \addons\drama\model\User::where('id', $operId)->field('nickname as name, avatar')->find();
            $operator['type'] = '用户';
        } else {
            $operator = [
                'name' => '系统',
                'avatar' => '',
                'type' => '系统'
            ];
        }
        if(!isset($operator['name'])) {
            $operator['name'] = '已删除';
            $operator['avatar'] = '';
        }
        return $operator;
    }
}
