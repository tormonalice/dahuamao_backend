<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Model;


class Vip extends Model
{

    // 表名
    protected $name = 'drama_vip';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text'
    ];

    
    public function getTypeList()
    {
        return ['d' => __('Type d'), 'm' => __('Type m'), 'q' => __('Type q'), 'y' => __('Type y')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getContentAttr($value, $data){
        $value = $value ? json_decode($value, true) : (isset($data['content']) && $data['content'] ?
            json_decode($data['content'], true) : null);
        return $value;
    }
}
