<?php

namespace app\admin\model\drama;

use think\Model;
use traits\model\SoftDelete;

class Cryptocard extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_cryptocard';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
        'status_text'
    ];
    

    
    public function getTypeList()
    {
        return ['vip' => __('Type vip'), 'reseller' => __('Type reseller'), 'usable' => __('Type usable')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setUsetimestartAttr($value, $data)
    {
        $usetimeArray = explode(' - ', $data['usetime']);
        return strtotime($usetimeArray[0]);
    }
    protected function setUsetimeendAttr($value, $data)
    {
        $usetimeArray = explode(' - ', $data['usetime']);
        return strtotime($usetimeArray[1]);
    }

}
