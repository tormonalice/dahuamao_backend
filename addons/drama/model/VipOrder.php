<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Db;
use think\Model;
use addons\drama\exception\Exception;


class VipOrder extends Model
{
    // 表名
    protected $name = 'drama_vip_order';
    
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

    // 订单状态
    const STATUS_INVALID = -2;
    const STATUS_CANCEL = -1;
    const STATUS_NOPAY = 0;
    const STATUS_PAYED = 1;
    const STATUS_FINISH = 2;


    public function getStatusList()
    {
        return ['-2' => __('Status -2'), '-1' => __('Status -1'), '0' => __('Status 0'), '1' => __('Status 1'), '2' => __('Status 2')];
    }

    public function getPayTypeList()
    {
        return ['wechat' => __('Pay_type wechat'), 'alipay' => __('Pay_type alipay'), 'wallet' => __('Pay_type wallet'), 'score' => __('Pay_type score'), 'cryptocard' => __('Pay_type cryptocard'), 'system' => __('Pay_type system')];
    }

    public function getPlatformList()
    {
        return ['H5' => __('Platform h5'), 'wxOfficialAccount' => __('Platform wxofficialaccount'), 'wxMiniProgram' => __('Platform wxminiprogram'), 'Web' => __('Platform web')];
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


    public function vip()
    {
        return $this->belongsTo('Vip', 'vip_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }


    // 获取订单号
    public static function getSn($user_id)
    {
        $rand = $user_id < 9999 ? mt_rand(100000, 99999999) : mt_rand(100, 99999);
        $order_sn = date('Yhis') . $rand;

        $id = str_pad($user_id, (24 - strlen($order_sn)), '0', STR_PAD_BOTH);

        return 'TO' . $order_sn . $id;
    }

    // 购买记录
    public static function getList($params)
    {
        $user = User::info();
        extract($params);

        $orders = self::with('vip')
            ->where('site_id', $user->site_id)
            ->where('user_id', $user->id)
            ->where('status', 'in', [1,2])
            ->order('id', 'desc')
            ->paginate(10);

        return $orders;
    }


    // 订单详情
    public static function detail($params)
    {
        $user = User::info();
        extract($params);

        $order = self::with('vip')->where('user_id', $user->id)->where('site_id', $user->site_id);

        if (isset($order_sn)) {
            $order = $order->where('order_sn', $order_sn);
        }
        if (isset($id)) {
            $order = $order->where('id', $id);
        }

        $order = $order->find();

        if (!$order) {
            new Exception('订单不存在');
        }

        return $order;
    }

    // 创建订单
    public static function recharge($params)
    {
        $user = User::info();

        // 入参
        extract($params);
        $total_fee = floatval($total_fee);

        $vip = Vip::where('id', $vip_id)->where('status', '1')->find();
        if (empty($vip)) {
            new Exception('VIP套餐不存在');
        }
        if ($total_fee < 0.01 || $vip['price'] != $total_fee) {
            new Exception('请输入正确的金额');
        }

        $times = 0;
        switch ($vip['type']){
            case 'd':
                $times = $vip['num'] * 86400;
                break;
            case 'm':
                $times = $vip['num'] * 86400 * 30;
                break;
            case 'q':
                $times = $vip['num'] * 86400 * 30 * 3;
                break;
            case 'y':
                $times = $vip['num'] * 86400 * 365;
                break;

        }

        $close_time = 10;

        $orderData = [];
        $orderData['order_sn'] = self::getSn($user->id);
        $orderData['site_id'] = $user->site_id;
        $orderData['user_id'] = $user->id;
        $orderData['vip_id'] = $vip_id;
        $orderData['status'] = 0;
        $orderData['total_fee'] = $total_fee;
        $orderData['times'] = $times;
        $orderData['remark'] = $remark ?? null;
        $orderData['platform'] = $platform;
        $orderData['ext'] = json_encode(['expired_time' => time() + ($close_time * 60)]);

        $orderData['product_id'] = $vip['product_id'];

        $order = new VipOrder();
        $order->allowField(true)->save($orderData);

        // \think\Queue::later(($close_time * 60), '\addons\drama\job\TradeOrderAutoOper@autoClose', ['order' => $order], 'drama');

        return $order;
    }


    /**
     * 订单支付成功
     *
     * @param [type] $order
     * @param [type] $notify
     * @return void
     */
    public function paymentProcess($order, $notify)
    {
        $vip = Vip::where('id', $order->vip_id)->where('status', '1')->find();
        // 判断订单合法性
        if($notify['pay_fee'] != $vip['price']){
            $msg = "VIP套餐金额({$notify['pay_fee']})与支付金额({$vip['price']})不一致";
            $order->remark = $msg;
            $order->save();
            new Exception($msg);
        }

        try {
            Db::startTrans();
            // 添加vip到期时间
            $vip_user = User::where('id', $order->user_id)->lock(true)->find();
            if($vip_user['vip_expiretime'] < time()){
                $vip_user->vip_expiretime = strtotime(date('Y-m-d', strtotime('+1 day'))) + $order->times;
            }else{
                $vip_user->vip_expiretime = $vip_user['vip_expiretime'] + $order->times;
            }
            $vip_user->save();

            $order->status = 1;
            $order->paytime = time();
            $order->transaction_id = $notify['transaction_id'];
            $order->payment_json = $notify['payment_json'];
            $order->pay_type = $notify['pay_type'];
            $order->pay_fee = $notify['pay_fee'];
            $order->save();

            $share = ['order'=>$order, 'order_type'=>'vip'];
            \think\Hook::listen('finish_after', $share);
            Db::commit();
        }catch (\think\Exception $e){
            Db::rollback();
            new Exception('增加VIP时间和保存订单出错：'.$e->getMessage());
        }

        return $order;
    }

    public function setExt($order, $field, $origin = [])
    {
        $newExt = array_merge($origin, $field);

        $orderExt = $order['ext_arr'];

        return array_merge($orderExt, $newExt);
    }

}
