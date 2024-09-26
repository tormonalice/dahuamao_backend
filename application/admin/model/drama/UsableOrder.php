<?php

namespace app\admin\model\drama;

use think\Model;
use traits\model\SoftDelete;

class UsableOrder extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_usable_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'status_text',
        'pay_type_text',
        'paytime_text',
        'platform_text'
    ];
    

    
    public function getStatusList()
    {
        return ['-2' => __('Status -2'), '-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
    }

    public function getPayTypeList()
    {
        return ['wechat' => __('Pay_type wechat'), 'alipay' => __('Pay_type alipay'), 'wallet' => __('Pay_type wallet'), 'score' => __('Pay_type score'), 'cryptocard' => __('Pay_type cryptocard'), 'system' => __('Pay_type system'),'douyinpay'=>'抖音支付'];
    }

    public function getPlatformList()
    {
        return ['H5' => __('Platform h5'), 'wxOfficialAccount' => __('Platform wxofficialaccount'), 'wxMiniProgram' => __('Platform wxminiprogram'), 'Web' => __('Platform web'),'douyinxcx'=>'抖音小程序'];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPayTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_type']) ? $data['pay_type'] : '');
        $list = $this->getPayTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPaytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['paytime']) ? $data['paytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getPlatformTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['platform']) ? $data['platform'] : '');
        $list = $this->getPlatformList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setPaytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function usables()
    {
        return $this->belongsTo('Usable', 'usable_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
