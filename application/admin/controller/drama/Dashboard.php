<?php

namespace app\admin\controller\drama;

use app\common\controller\Backend;
use think\Db;

/**
 * 控制台
 *
 * @icon fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['sync'];

    /**
     * 查看
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            $datetimerange = explode(' - ', $this->request->request('datetimerange'));
            $startTime = strtotime($datetimerange[0]);
            $endTime = strtotime($datetimerange[1]);
            $where = [
                'createtime' => ['between', [$startTime, $endTime]]
            ];
            // 支付渠道
            //$data['paypalPay'] = 0;
            $data['wechatPay'] = 0;
            $data['allTypePay'] = 0;
            // 统计订单
            $data['orderNum'] = 0;
            $data['orderArr'] = [];

            // Vip订单
            $list_vip = \addons\drama\model\VipOrder::where($where)->select();
            $data['vipPayOrderNum'] = 0;
            $data['vipPayOrderArr'] = [];
            $data['orderNum'] += count($list_vip);
            $data = $this->getTotalData($list_vip, $data, 'vip');
            // 分销订单
            $list_reseller = \addons\drama\model\ResellerOrder::where($where)->select();
            $data['resellerPayOrderNum'] = 0;
            $data['resellerPayOrderArr'] = [];
            $data['orderNum'] += count($list_reseller);
            $data = $this->getTotalData($list_reseller, $data, 'reseller');
            // 积分订单
            $list_usable = \addons\drama\model\UsableOrder::where($where)->select();
            $data['usablePayOrderNum'] = 0;
            $data['usablePayOrderArr'] = [];
            $data['orderNum'] += count($list_usable);
            $data = $this->getTotalData($list_usable, $data, 'usable');
            // 提现订单
            $walletApplyNum = 0;
            $list_wallet_apply = \addons\drama\model\UserWalletApply::where($where)->select();
            $data['walletApplyPayOrderNum'] = count($list_wallet_apply);
            $data['walletApplyPayOrderArr'] = [];
            $data['walletPayOrderNum'] = 0;
            $data['walletPayOrderArr'] = [];
            foreach ($list_wallet_apply as $order){
                $data['walletApplyPayOrderArr'][] = [
                    'counter' => 1,
                    'createtime' => $order['createtime'] * 1000,
                    'user_id' => $order['user_id']
                ];
                if($order['status'] != -1){
                    $data['walletPayOrderNum'] += bcadd($order['money'], $order['charge_money'], 0);
                    $data['walletPayOrderArr'][] = [
                        'counter' => bcadd($order['money'], $order['charge_money'], 0),
                        'createtime' => $order['createtime'] * 1000,
                    ];
                }
                if($order['status'] > 0){
                    $walletApplyNum += bcadd($order['money'], $order['charge_money'], 0);
                }
            }
            // 佣金记录
            $list_reseller_log = \addons\drama\model\ResellerLog::where($where)->select();
            $data['resellerLogPayOrderNum'] = 0;
            $data['resellerLogPayOrderArr'] = [];
            foreach ($list_reseller_log as $order){
                $data['resellerLogPayOrderNum'] += $order['pay_money'];
                $data['resellerLogPayOrderArr'][] = [
                    'counter' => $order['pay_money'],
                    'createtime' => $order['createtime'] * 1000,
                ];
            }
            // 视频列表
            $vid_sales = \addons\drama\model\VideoOrder::field('vid, sum(total_fee) as total_fee, count(*) as sales')
                ->where('site_id', $this->auth->id)
                ->whereExists(function ($query) {
                    $video_table_name = (new \addons\drama\model\Video())->getQuery()->getTable();
                    $query->table($video_table_name)->where('vid=' . $video_table_name . '.id');
                })
                ->group('vid')
                ->order('total_fee', 'desc')
                ->limit(5)
                ->select();
            $vid_sales = collection($vid_sales)->toArray();
            $videoList = [];
            foreach ($vid_sales as $key=>$value){
                $item = Db::name('drama_video')->where('id', $value['vid'])->find();
                $item['total_fee'] = $value['total_fee'];
                $item['sales'] = $item['sales'] + $value['sales'];
                $videoList[] = $item;
            }
            $data['videoList'] = $videoList;
            // 支付单数 / 总单数
            $order_payed_num = $data['vipPayOrderNum'] + $data['resellerPayOrderNum'] + $data['usablePayOrderNum'];
            $data['orderFinish'] = [
                'order_scale' => $data['orderNum'] ? round(($order_payed_num / $data['orderNum']), 2) : 0,
                'order_payed' => $order_payed_num,
            ];
            // 提现积分 / 佣金积分
            $data['payedFinish'] = [
                'payed_scale' => $data['resellerLogPayOrderNum'] ? round(($walletApplyNum / $data['resellerLogPayOrderNum']), 2) : 0,
                'payed_money' => $walletApplyNum
            ];

            $this->success('数据中心', '', $data);
        }

        return $this->view->fetch();
    }



    private function orderScale ($list) {
        $total = count($list);
        $total_money = array_sum(array_column($list, 'total_fee'));

        $data['orderFinish'] = [
            'order_scale' => 0,
            'order_user' => 0
        ];
        $data['payedFinish'] = [
            'payed_scale' => 0,
            'payed_money' => 0
        ];

        // 支付单数
        $payed_num = 0;
        // 支付金额
        $payed_money = 0;
        // 支付的用户 id
        $payed_user_ids = [];

        foreach ($list as $key => $order) {
            if ($order['status'] > 0) {
                $payed_num++;
                $payed_money = bcadd($payed_money, $order['total_fee'], 2);
                $payed_user_ids[] = $order['user_id'];
            }
        }

        $orderFinish = [
            'order_scale' => $total ? round(($payed_num / $total), 2) : 0,
            'order_payed' => $payed_num,
        ];

        $payedFinish = [
            'payed_scale' => $total_money ? round(($payed_money / $total_money), 2) : 0,
            'payed_money' => round($payed_money, 2)
        ];

        return compact("orderFinish", "payedFinish");
    }


    private function getTotalData($list, $data, $type) {
        foreach ($list as $key => $order) {
            $data['orderArr'][] = [
                'counter' => 1,
                'createtime' => $order['createtime'] * 1000,
                'user_id' => $order['user_id']
            ];

            if ($order['status'] > 0) {
                $data[$type.'PayOrderNum']++;

                $data[$type.'PayOrderArr'][] = [
                    'counter' => 1,
                    'createtime' => $order['createtime'] * 1000,
                    'user_id' => $order['user_id']
                ];

                $data['allTypePay']++;
                if ($order['pay_type'] == 'paypal') {
                    $data['paypalPay']++;
                }
                if ($order['pay_type'] == 'wechat') {
                    $data['wechatPay']++;
                }
            }
        }

        return $data;
    }
}
