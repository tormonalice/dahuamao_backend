<?php

namespace addons\drama\model;

use think\Model;


class UserSign extends Model
{

    // 表名
    protected $name = 'drama_user_sign';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
    ];

    protected $type = [
        'rules' => 'json'
    ];

}