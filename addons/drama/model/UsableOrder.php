<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Db;
use think\Model;
use addons\drama\exception\Exception;


class UsableOrder extends Model
{
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


    public function package()
    {
        return $this->belongsTo('Usable', 'usable_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }


    // 获取订单号
    public static function getSn($user_id)
    {
        $rand = $user_id < 9999 ? mt_rand(100000, 99999999) : mt_rand(100, 99999);
        $order_sn = date('Yhis') . $rand;

        $id = str_pad($user_id, (24 - strlen($order_sn)), '0', STR_PAD_BOTH);

        return 'AO' . $order_sn . $id;
    }

    // 购买记录
    public static function getList($params)
    {
        $user = User::info();
        extract($params);
        $site_id = Config::getSiteId();
        $orders = self::with('package')
            ->where('site_id', $site_id)
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

        $site_id = Config::getSiteId();
        $order = self::with('package')->where('user_id', $user->id)->where('site_id', $site_id);

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

        $usable = Usable::where('id', $usable_id)->where('status', '1')->find();
        if (empty($usable)) {
            new Exception('充值套餐不存在');
        }
        if ($total_fee < 0.01 || $usable['price'] != $total_fee) {
            new Exception('请输入正确的金额');
        }

        $close_time = 10;

        $orderData = [];
        $orderData['order_sn'] = self::getSn($user->id);
        $orderData['site_id'] = $user->site_id;
        $orderData['user_id'] = $user->id;
        $orderData['usable_id'] = $usable_id;
        $orderData['status'] = 0;
        $orderData['total_fee'] = $total_fee;
        $orderData['usable'] = $usable['usable'];
        $orderData['remark'] = $remark ?? null;
        $orderData['platform'] = $platform;
        $orderData['ext'] = json_encode(['expired_time' => time() + ($close_time * 60)]);

        $orderData['product_id'] = $usable['product_id'];
        $orderData['video_id'] = $video_id??null;
        $orderData['auto_num'] = $auto_num??null;
        $orderData['rate'] = $rate??null;

        $order = new UsableOrder();
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
        $usable = Usable::where('id', $order->usable_id)->where('status', '1')->find();
        // 判断订单合法性
        if($notify['pay_fee'] != $usable['price']){
            $msg = "充值套餐金额({$notify['pay_fee']})与支付金额({$usable['price']})不一致";
            $order->remark = $msg;
            $order->save();
            new Exception($msg);
        }

        try {
            Db::startTrans();
            // 添加usable次数
            $user = User::where('id', $order->user_id)->lock(true)->find();
            User::usable($order->usable, $user, 'recharge_usable', $order->id, '购买充值套餐充值剧场积分',[
                        'order_id' => $order->id,
                        'order_sn' => $order->order_sn,
                    ]);

            $order->status = 1;
            $order->paytime = time();
            $order->transaction_id = $notify['transaction_id'];
            $order->payment_json = $notify['payment_json'];
            $order->pay_type = $notify['pay_type'];
            $order->pay_fee = $notify['pay_fee'];
            $order->save();

            $share = ['order'=>$order, 'order_type'=>'usable'];
            \think\Hook::listen('finish_after', $share);
            Db::commit();
        }catch (\think\Exception $e){
            Db::rollback();
            new Exception('增加剧场积分和保存订单出错：'.$e->getMessage());
        }

        if($order->video_id && $order->auto_num){
            $time = time();
            $e_list = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $order->video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->field('ve.id,ve.price,ve.vprice')
                ->order('weigh desc')
                ->limit($order->auto_num)
                ->select();

            foreach($e_list as $v){
                Db::startTrans();
                try{
                    if($order->rate){
                        if($user->vip_expiretime > time()){
                            $youhui = bcmul(bcdiv($v['vprice'],100,2),$order->rate,0);
                            $price = $v['vprice'] - $youhui;
                        }else{
                            $youhui = bcmul(bcdiv($v['price'],100,2),$order->rate,0);
                            $price = $v['price'] - $youhui;
                        }
                    }else{
                        if($user->vip_expiretime > time()){
                            $price = $v['vprice'];
                        }else{
                            $price = $v['price'];
                        }
                    }

                    $user = User::where('id', $user->id)->lock(true)->find();
                    $data = [
                        'site_id' => $user->site_id,
                        'vid' => $order->video_id,
                        'episode_id' => $v['id'],
                        'order_sn' => self::getSn($user->id),
                        'user_id' => $user->id,
                        'total_fee' => $price,
                        'platform' => $order->platform,
                    ];
                    $video_order = VideoOrder::create($data);
                    VideoEpisodes::where('id', $v['id'])->setInc('sales');
                    User::usable(-$price, $user, 'used_video', $video_order->id, '充积分批量购买', [
                        'video_order_id'=>$video_order->id,
                        'usable_order_id'=>$order->id,
                        'video_id' => $order->video_id,
                        'video_episodes_id' => $v['id'],
                    ]);
                    Db::commit();
                }catch (\think\Exception $e){
                    Db::rollback();
                    //echo $e->getMessage();
                }


            }
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
