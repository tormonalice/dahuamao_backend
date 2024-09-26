<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Model;

class Usable extends Model
{
    // 表名
    protected $name = 'drama_usable';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    protected $hidden = ['createtime', 'updatetime', 'status', 'weigh'];

    // 追加属性
    protected $append = [
    ];

    public function getContentAttr($value, $data){
        $value = $value ? json_decode($value, true) : (isset($data['content']) && $data['content'] ?
            json_decode($data['content'], true) : null);
        return $value;
    }

}
