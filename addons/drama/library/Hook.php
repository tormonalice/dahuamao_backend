<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\library;

class Hook
{

    public function __construct()
    {
        
    }

    public static function register ($behaviors = []) {
        $default = require ROOT_PATH . 'addons/drama/hooks.php';

        $behaviors = array_merge($default, $behaviors);
        foreach ($behaviors as $tag => $behavior) {
            // 数组反转 保证最上面的行为优先级最高    
            $behavior = array_reverse($behavior);
            foreach ($behavior as $be) {
                \think\Hook::add($tag, $be, true);      // 所有行为都插入最前面
            }
        }
    }
}
