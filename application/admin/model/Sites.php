<?php

namespace app\admin\model;

use think\Model;


class Sites extends Model
{

    // 表名
    protected $name = 'sites';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'expiretime_text'
    ];

    public function getOriginData()
    {
        return $this->origin;
    }

    protected static function init()
    {
        self::beforeDelete(function ($row) {
            //如果主站点禁止删除
            if (isset($row['is_default'])) {
                if ($row['is_default'] == 1) {
                    throw new \think\Exception('默认站点禁止删除！');
                }
            }
        });
        self::beforeWrite(function ($row) {
            $changed = $row->getChangedData();
            $origin = $row->getOriginData();
            //如果有修改默认
            if (isset($changed['is_default'])) {
                if ($changed['is_default'] == 1) {
                    self::where('site_id', '<>', $row['site_id'])->update(['is_default'=>0]);
                }
                if(isset($origin['is_default']) && $origin['is_default'] == 1 && $changed['is_default'] == 0){
                    throw new \think\Exception('请至少设置一个默认站点！');
                }
                if(!self::find() && $changed['is_default'] == 0){
                    throw new \think\Exception('请至少设置一个默认站点！');
                }
            }
        });
    }
    
    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }

    public function getIsDefaultList()
    {
        return [0 => __('普通站点'), 1 => __('默认站点')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getExpiretimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['expiretime']) ? $data['expiretime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setExpiretimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function admin()
    {
        return $this->belongsTo('Admin', 'site_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
