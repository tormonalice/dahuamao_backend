<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\ResellerBind;
use addons\drama\model\User;
use think\Cache;
use think\Db;

/**
 * 卡密
 * Class Cryptocard
 * @package addons\drama\controller
 */
class Cryptocard extends Base
{
    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 卡密兑换
     * @ApiParams   (name="crypto", type="string", required=true, description="卡密")
     * @ApiParams   (name="platform", type="string", required=true, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function decrypt_card(){
        $this->repeat_filter();
        $count = 1;
        if(Cache::has('decrypt_card_user_id_'.$this->auth->id)){
            $count = Cache::get('decrypt_card_user_id_'.$this->auth->id) + 1;
            if($count > 5){
                $this->error('输入错误次数超过5次，请一个小时后再试！');
            }
        }
        $pwd = $this->request->get('crypto');
        $platform = $this->request->get('platform', 'H5');
        $card = $this->match;
        $site_id = $this->site_id;
        $crypto_card = Db::name('drama_cryptocard')
            ->where('pwd', $pwd)
            ->where('site_id', $site_id)
            ->whereNull('deletetime')
            ->find();
        if(empty($crypto_card)){
            Cache::set('decrypt_card_user_id_'.$this->auth->id, $count, 3600);
            $this->error('卡密不存在，请输入正确的卡密！');
        }else{
            $this->$card();
        }
        if($crypto_card['status'] != 0){
            Cache::set('decrypt_card_user_id_'.$this->auth->id, $count, 3600);
            $this->error('当前卡密已兑换，请勿重复兑换！');
        }
        if(time() > $crypto_card['usetimeend']){
            Cache::set('decrypt_card_user_id_'.$this->auth->id, $count, 3600);
            $this->error('当前卡密已失效！');
        }
        if(time() < $crypto_card['usetimestart']){
            Cache::set('decrypt_card_user_id_'.$this->auth->id, $count, 3600);
            $this->error('当前卡密未到兑换时间：'.date('Y-m-d', $crypto_card['usetimestart']).'！');
        }

        if($crypto_card['type'] == 'vip'){
            $this->changeVip($crypto_card['item_id'], $crypto_card['id'], $platform);
        }elseif($crypto_card['type'] == 'reseller'){
            $this->changeReseller($crypto_card['item_id'], $crypto_card['id'], $platform);
        }elseif($crypto_card['type'] == 'usable'){
            $this->changeUsable($crypto_card['item_id'], $crypto_card['id'], $platform);
        }else{
            Cache::set('decrypt_card_user_id_'.$this->auth->id, $count, 3600);
            $this->error('卡密异常，无法兑换！');
        }

    }


    /**
     * 兑换vip
     */
    private function changeVip($id,$cryptocard_id)
    {
        $vip = \app\admin\model\drama\Vip::get($id);
        if (!$vip) {
            $this->error('卡密异常，无法兑换！');
        }

        Db::startTrans();
        try {
            $crypto_card = Db::name('drama_cryptocard')
                ->where('id', $cryptocard_id)
                ->lock(true)
                ->find();
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
            $user = $this->auth->getUser();
            $order = new \addons\drama\model\VipOrder();
            $orderData = [];
            $orderData['order_sn'] = $order::getSn($user->id);
            $orderData['site_id'] = $user->site_id;
            $orderData['user_id'] = $user->id;
            $orderData['vip_id'] = $id;
            $orderData['status'] = 1;
            $orderData['total_fee'] = $vip['price'];
            $orderData['times'] = $times;
            $orderData['remark'] = '卡密兑换';
            $orderData['pay_type'] = 'cryptocard';
            $orderData['paytime'] = time();
            $order->allowField(true)->save($orderData);
            if($user['vip_expiretime'] < time()){
                $user->vip_expiretime = strtotime(date('Y-m-d', strtotime('+1 day'))) + $order->times;
            }else{
                $user->vip_expiretime = $user['vip_expiretime'] + $order->times;
            }
            $user->save();
            Db::name('drama_cryptocard')
                ->where('id', $cryptocard_id)
                ->update(['status'=>1]);
            Db::name('drama_user_cryptocard')->insert([
                'user_id' => $user->id,
                'cryptocard_id' => $cryptocard_id,
                'type' => $crypto_card['type'],
                'order_id' => $order->id,
                'createtime' => time()
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('卡密兑换失败，请稍后重试！');
        }

        $this->success('成功');
    }

    /**
     * 兑换分销商
     */
    private function changeReseller($id,$cryptocard_id)
    {
        $reseller = \app\admin\model\drama\Reseller::get($id);
        if (!$reseller) {
            $this->error('卡密异常，无法兑换！');
        }

        Db::startTrans();
        try {
            $crypto_card = Db::name('drama_cryptocard')
                ->where('id', $cryptocard_id)
                ->lock(true)
                ->find();
            $order = new \addons\drama\model\ResellerOrder();
            $user = $this->auth->getUser();
            $orderData = [];
            $orderData['site_id'] = $user->site_id;
            $orderData['order_sn'] = $order::getSn($user->id);
            $orderData['user_id'] = $user->id;
            $orderData['reseller_id'] = $id;
            $orderData['status'] = 1;
            $orderData['total_fee'] = $reseller['price'];
            $orderData['times'] = $reseller['expire'];
            $orderData['remark'] = '卡密兑换';
            $orderData['pay_type'] = 'cryptocard';
            $orderData['paytime'] = time();
            $order->allowField(true)->save($orderData);
            // 添加reseller到期时间
            ResellerBind::add($order->site_id, $order->user_id, $order->reseller_id);
            Db::name('drama_cryptocard')
                ->where('id', $cryptocard_id)
                ->update(['status'=>1]);
            Db::name('drama_user_cryptocard')->insert([
                'user_id' => $user->id,
                'cryptocard_id' => $cryptocard_id,
                'type' => $crypto_card['type'],
                'order_id' => $order->id,
                'createtime' => time()
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('卡密兑换失败，请稍后重试！');
        }

        $this->success('成功');
    }

    /**
     * 兑换剧场积分
     */
    private function changeUsable($id,$cryptocard_id)
    {
        $usable_info = \app\admin\model\drama\Usable::get($id);
        if (!$usable_info) {
            $this->error('卡密异常，无法兑换！');
        }

        Db::startTrans();
        try {
            $crypto_card = Db::name('drama_cryptocard')
                ->where('id', $cryptocard_id)
                ->lock(true)
                ->find();
            $order = new \addons\drama\model\UsableOrder();
            $user = $this->auth->getUser();
            $orderData = [];
            $orderData['site_id'] = $user->site_id;
            $orderData['order_sn'] = $order::getSn($user->id);
            $orderData['user_id'] = $user->id;
            $orderData['usable_id'] = $id;
            $orderData['status'] = 1;
            $orderData['total_fee'] = $usable_info['price'];
            $orderData['usable'] = $usable_info['usable'];
            $orderData['remark'] = '卡密兑换';
            $orderData['pay_type'] = 'cryptocard';
            $orderData['paytime'] = time();
            $order->allowField(true)->save($orderData);
            // 添加usable次数
            $user = User::where('id', $order->user_id)->lock(true)->find();
            User::usable($order->usable, $user, 'cryptocard', $order->id, '卡密兑换充值套餐充值剧场积分',[
                'order_id' => $order->id,
                'order_sn' => $order->order_sn,
            ]);
            Db::name('drama_cryptocard')
                ->where('id', $cryptocard_id)
                ->update(['status'=>1]);
            Db::name('drama_user_cryptocard')->insert([
                'user_id' => $user->id,
                'cryptocard_id' => $cryptocard_id,
                'type' => $crypto_card['type'],
                'order_id' => $order->id,
                'createtime' => time()
            ]);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            $this->error('卡密兑换失败，请稍后重试！');
        }

        $this->success('成功');
    }

}