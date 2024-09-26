<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\listener;

use addons\drama\model\ResellerBind;
use addons\drama\model\ResellerLog;
use addons\drama\model\Share as ShareModel;
use addons\drama\model\User;
use think\Db;

/**
 * 分销
 */
class Reseller
{
    /**
     * 注册后推广关系保存
     * 注意user要求必须是登录用户，必须放到事务中执行
     * 使用spm方法拼接 shareUserId(分享者用户ID).page(页面类型:1=首页(默认),2=添加(手动)).pageId(页面ID).platform(平台渠道: 1=H5,2=微信公众号网页,3=微信小程序,4=Web,5=Admin,6=App).from(分享方式: 1=直接转发,2=海报,3=链接,4=补录)
     * 例 spm=258.1.0.3.2 即为ID为258用户通过微信小程序平台生成了首页分享海报
     */
    public function registerAfter($params){
        extract($params);
        $share = ShareModel::add($spm, $platform);
        if($share){
            $share_id = $share['share_id'];
            \think\Hook::listen('share_success', $share_id);
            $user = User::info();
            User::where('id', $user->id)->update(['parent_user_id'=>$share['share_id']]);
            \addons\drama\model\Reseller::share_user_reseller($share, $user);
        }
    }

    /**
     * 订单完成后
     * [order, order_type]
     */
    public function finishAfter($param){
        extract($param);
        if(in_array($order['status'], [1,2])){
            $reseller_user = Db::name('drama_reseller_user')
                ->where('user_id', $order['user_id'])
                ->select();
            foreach ($reseller_user as $item){
                $reseller_bind = ResellerBind::where('user_id', $item['reseller_user_id'])->find();
                if(empty($reseller_bind)){
                    return false;
                }
                if($reseller_bind['expiretime'] != 0 && $reseller_bind['expiretime'] < time()){
                    return false;
                }
                $reseller = json_decode($reseller_bind['reseller_json'], true);
                $params = [
                    'reseller_user_id' => $item['reseller_user_id'],
                    'site_id' => $order['site_id'],
                    'user_id' => $order['user_id'],
                    'pay_money' => $order['pay_fee'],
                    'order_type' => $order_type,
                    'order_id' => $order['id']
                ];
                $msg = '';
                if($item['type'] == '1'){
                    $params['type'] = 'direct';
                    $params['ratio'] = $reseller['direct'];
                    // 佣金收益
                    $reseller_money = bcmul($order['pay_fee'], $reseller['direct']/100, 2);
                    $msg = '直接分销佣金';
                }elseif($item['type'] == '2'){
                    $params['type'] = 'indirect';
                    $params['ratio'] = $reseller['indirect'];
                    // 佣金收益
                    $reseller_money = bcmul($order['pay_fee'], $reseller['indirect']/100, 2);
                    $msg = '间接分销佣金';
                }
                $params['money'] = $reseller_money;
                $reseller_log = ResellerLog::create($params);
                if($reseller_money > 0){
                    \addons\drama\model\User::money($reseller_money, $item['reseller_user_id'], 'commission_income',
                        $reseller_log->id, $msg, [
                            'reseller_log_id' => $reseller_log->id,
                            'user_id' => $reseller_log->user_id,
                            'parent_id' => $item['parent_id'],
                            'reseller_user_id' => $reseller_log->reseller_user_id
                        ]);
                }
            }
        }
    }

}
