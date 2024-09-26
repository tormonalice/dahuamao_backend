<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\exception\Exception;

/**
 * 流水
 * Class UserWalletLog
 * @package addons\drama\controller
 */
class UserWalletLog extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];


    /**
     * 流水记录
     * @ApiParams   (name="wallet_type", type="string", required=true, description="标识：usable 剧场积分记录")
     * @ApiParams   (name="status", type="string", required=true, description="收支：all全部add收入reduce支出")
     * @ApiParams   (name="date", type="string", required=true, description="时间：格式(20230501-20230531)默认当月月")
     */
    public function index()
    {
        $params = $this->request->get();
        $params = array_filter($params);
        $wallet_type = $params['wallet_type'] ?? 'usable';
        if (!in_array($wallet_type, ['money', 'score', 'usable'])) {
            $this->error('参数错误');
        }
        $wallet_name = [
            'money'=>'钱包记录',
            'score'=>'积分记录',
            'usable'=>'剧场积分记录'
        ];
        $this->success($wallet_name[$wallet_type], \addons\drama\model\UserWalletLog::getList($params));
    }

}
