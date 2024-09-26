<?php

namespace addons\drama\controller;

use addons\drama\library\UserSignService;
use addons\drama\model\Config;
use addons\drama\model\Richtext;
use app\admin\model\drama\Turntable;
use app\admin\model\drama\TurntableNum;
use addons\drama\model\UserWalletLog;
use addons\drama\model\User;

/**
 * 每日签到
 * Class UserSign
 * @package addons\drama\controller
 */
class UserSign extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];
    protected $noNeedGuest = ['signin', 'replenish'];

    /**
     * 签到列表
     * @ApiParams   (name="month", type="string", required=false, description="月份：2024-1")
     */
    public function index()
    {
        $params = $this->request->param();
        $month = (isset($params['month']) && $params['month']) ? date('Y-m', strtotime($params['month'])) : date('Y-m');     // 前端可能传来 2023-1,这里再统一格式化一下 month

        $is_current = ($month == date('Y-m')) ? true : false;
        $signin = new UserSignService();
        // 当前月，获取连续签到天数
        $continue_days = $signin->getContinueDays();

        $days = $signin->getList($month, $continue_days);

        $rules = $signin->getRules();

        $data = compact('days', 'continue_days', 'rules', 'is_current');

        Config::where(['site_id'=>$this->site_id,'name'=>'usersign'])->value('value');

        $turntable = Turntable::where('site_id',$this->site_id)->field('id,usable as name,image as img')->order('weigh desc')->select();

        if(!empty($turntable)){
            foreach($turntable as &$v){
                $v['img'] = cdnurl($v['img'],true);
            }
        }
        $data['turntable'] = $turntable;

        $data['guankan'] = TurntableNum::where(['site_id'=>$this->site_id,'user_id'=>$this->auth->id])->whereTime('createtime','today')->count();

        $data['yichou'] = UserWalletLog::where(['site_id'=>$this->site_id,'user_id'=>$this->auth->id,'type'=>'turntable'])->whereTime('createtime','today')->count();

        $data['mianfei'] = ($rules['mianfei']??0)?$rules['mianfei']:0;

        $data['shengyu'] = $rules['mianfei'] + $data['guankan'] - $data['yichou'];

        $zuigao = Turntable::where('site_id',$this->site_id)->order('usable desc')->value('usable');

        $data['zuigao'] = $zuigao?$zuigao+0:0;

        $data['usable'] = \addons\drama\model\User::where('id',$this->auth->id)->value('usable');

        $data['yue'] = User::where('id',$this->auth->id)->value('usable');

        $data['choujiang_rules'] = Richtext::where('id',$rules['choujiang_rules'])->find();

        $this->success('Operation completed', $data);
    }


    /**
     * 签到
     */
    public function signin()
    {
        $signin = new UserSignService();
        $signin = $signin->signin();

        $this->success('签到成功', $signin);
    }


    /**
     * 补签
     * @ApiParams   (name="date", type="string", required=false, description="日期：2024-01-15")
     */
    public function replenish()
    {
        $params = $this->request->param();
        $this->Validate($params, \addons\drama\validate\UserSign::class, "replenish");

        $signin = new UserSignService();
        $signin = $signin->replenish($params);

        $this->success('补签成功', $signin);
    }

    /**
     * 抽奖
     */
    public function chou(){

        $signin = new UserSignService();

        $data = $signin->getRules();

        $data['guankan'] = TurntableNum::where(['site_id'=>$this->site_id,'user_id'=>$this->auth->id])->whereTime('createtime','today')->count();

        $data['yichou'] = UserWalletLog::where(['site_id'=>$this->site_id,'user_id'=>$this->auth->id,'type'=>'turntable'])->whereTime('createtime','today')->count();

        $data['shengyu'] = $data['mianfei'] + $data['guankan'] - $data['yichou'];

        if($data['shengyu'] < 1){
            $this->error('剩余抽奖次数不足');
        }

        $total = Turntable::where('site_id',$this->site_id)->sum('chance');
        if(!$total){
            $this->error('奖品暂未设置概率');
        }

        $rand = rand(1,$total);
        $turntable = Turntable::where(['site_id'=>$this->site_id,'chance'=>['neq',0]])->select();
        $prize = '';
        foreach($turntable as $v){
            if($rand <= $v['chance']){
                $prize = $v;
                break;
            }else{
                $rand -= $v['chance'];
            }
        }

        try{
            User::usable($v['usable'],$this->auth->id,'turntable',$prize['id'],'转盘抽奖',$prize);
        }catch(\Exception $e){
            $this->error($e->getMessage());
        }
        $this->success('ok',['prize'=>$prize]);

    }

    //看广告增加次数
    public function lookgg(){

        $signin = new UserSignService();

        $data = $signin->getRules();

        $num = TurntableNum::where(['site_id'=>$this->site_id,'user_id'=>$this->auth->id])->whereTime('createtime','today')->count();

        $res = false;
        if($num < $data['shangxian']){
            $res = TurntableNum::create([
                'site_id' => $this->site_id,
                'user_id' => $this->auth->id
            ]);
        }

        if($res){
            $this->success('ok');
        }else{
            $this->error('增加次数失败');
        }
    }

}