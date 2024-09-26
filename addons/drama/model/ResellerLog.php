<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Model;


class ResellerLog extends Model
{
    
    // 表名
    protected $name = 'drama_reseller_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'order_type_text'
    ];
    

    
    public function getTypeList()
    {
        return ['direct' => __('Type direct'), 'indirect' => __('Type indirect')];
    }

    public function getOrderTypeList()
    {
        return ['vip' => __('Order_type vip'), 'reseller' => __('Order_type reseller'), 'usable' => __('Order_type usable')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getOrderTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['order_type']) ? $data['order_type'] : '');
        $list = $this->getOrderTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function reseller()
    {
        return $this->belongsTo('app\admin\model\User', 'reseller_user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
