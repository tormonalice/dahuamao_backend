<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\model\drama;

use think\Model;


class Block extends Model
{

    // 表名
    protected $name = 'drama_block';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'parsetpl_text',
        'status_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getTypeList()
    {
        return ['focus' => __('Type focus'), 'side' => __('Type side')];
    }

    public function getParsetplList()
    {
        return ['0' => __('Parsetpl 0'), '1' => __('Parsetpl 1')];
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getParsetplTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['parsetpl']) ? $data['parsetpl'] : '');
        $list = $this->getParsetplList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getNameList()
    {
        return [
            'uniappindexfocus' => 'UniAPP首页焦点图',
            'uniappindexside' => 'UniAPP首页广告图',
            'uniappuserside' => 'UniAPP个人中心广告图',
        ];
    }


}
