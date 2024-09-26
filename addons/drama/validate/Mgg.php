<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\validate;

use think\Validate;

class Mgg extends Validate
{

    /**
     * 验证规则
     */
    protected $rule = [
        'mgg_id' => 'require',
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'mgg_id.require' => '请选择免广告类型',
    ];

    /**
     * 字段描述
     */
    protected $field = [];

    /**
     * 验证场景
     */
    protected $scene = [
        'recharge' => ['mgg_id'],
    ];
}
