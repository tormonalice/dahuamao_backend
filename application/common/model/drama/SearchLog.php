<?php

namespace app\common\model\drama;

use think\Model;


class SearchLog extends Model
{

    

    

    // 表名
    protected $name = 'drama_search_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'user_type_text'
    ];
    

    
    public function getUserTypeList()
    {
        return ['1' => __('User_type 1'), '2' => __('User_type 2')];
    }


    public function getUserTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['user_type']) ? $data['user_type'] : '');
        $list = $this->getUserTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function user()
    {
        return $this->belongsTo('app\common\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
