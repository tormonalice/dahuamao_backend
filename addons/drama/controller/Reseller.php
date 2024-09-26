<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\Config;
use addons\drama\model\Reseller as ResellerModel;
use addons\drama\model\ResellerLog;
use addons\drama\model\Richtext;
use think\Db;

/**
 * 分销管理
 * Class VipOrder
 * @package addons\drama\controller
 */
class Reseller extends Base
{
    protected $noNeedLogin = ['index', 'detail'];
    protected $noNeedRight = '*';

    /**
     * 分销商等级
     */
    public function index(){
        $list = ResellerModel::where('status', 'normal')
            ->where('site_id', $this->site_id)
            ->orderRaw('weigh desc, id asc')
            ->select();
        foreach ($list as $key=>$item){
            $list[$key]['image'] = $item['image'] ? cdnurl($item['image'], true) : '';
            $list[$key]['expire_text'] = $item['expire'] == 0 ? '永久' : intval($item['expire']/86400).'天';
        }
        $config = Config::where('name', 'drama')->where('site_id', $this->site_id)->value('value');
        $config = json_decode($config, true);
        $reseller_desc = null;
        if(isset($config['reseller_desc']) && $config['reseller_desc']){
            $reseller_desc = Richtext::get($config['reseller_desc']);
        }
        $this->success('分销商等级', ['list'=>$list, 'reseller_desc'=>$reseller_desc]);
    }

    /**
     * 分销等级详情
     * @ApiParams   (name="id", type="integer", required=true, description="分销等级ID")
     * @ApiParams   (name="level", type="integer", required=true, description="分销等级")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function detail(){
        $id = $this->request->get('id', 0);
        $level = $this->request->get('level', 0);
        if($id){
            $where['id'] = $id;
        }
        if($level){
            $where['level'] = $level;
        }
        $where['status'] = 'normal';
        $data = ResellerModel::where($where)->where('site_id', $this->site_id)->find();
        if(empty($data)){
            $this->error('分销商等级不存在！');
        }
        $data['image'] = $data['image'] ? cdnurl($data['image'], true) : '';
        $data['expire_text'] = $data['expire'] == 0 ? '永久' : intval($data['expire']/86400).'天';
        $this->success('分销等级详情', $data);
    }

    /**
     * 团队用户
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user(){
        $page = $this->request->get('page', 1);
        $pagesize = $this->request->get('pagesize', 10);
        $count = Db::name('drama_reseller_user')
            ->where('site_id', $this->site_id)
            ->where('reseller_user_id', $this->auth->id)
            ->count();
        $count_direct = Db::name('drama_reseller_user')
            ->where('site_id', $this->site_id)
            ->where('reseller_user_id', $this->auth->id)
            ->where('type', '1')
            ->count();
        $count_indirect = Db::name('drama_reseller_user')
            ->where('site_id', $this->site_id)
            ->where('reseller_user_id', $this->auth->id)
            ->where('type', '2')
            ->count();
        $reseller_user = Db::name('drama_reseller_user')
            ->alias('r')
            ->join('user u', 'r.user_id=u.id', 'left')
            ->field('r.*,u.nickname,u.avatar')
            ->where('r.site_id', $this->site_id)
            ->where('r.reseller_user_id', $this->auth->id)
            ->order('r.id', 'desc')
            ->page($page, $pagesize)
            ->select();
        foreach ($reseller_user as &$item){
            if($item['nickname'] == null && $item['avatar'] == null){
                $userConfig = json_decode(Config::get(['name' => 'user', 'site_id'=>$this->site_id])->value, true);
                $item['nickname'] = isset($userConfig['nickname']) ? $userConfig['nickname'].'**' : '匿名用户';
                $item['avatar'] = isset($userConfig['avatar']) ? cdnurl($userConfig['avatar'], true) : '';
            }
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
        }
        $this->success('团队用户', [
            'count' => $count,
            'count_direct' => $count_direct,
            'count_indirect' => $count_indirect,
            'reseller_user' => $reseller_user,
        ]);
    }

    /**
     * 分销记录
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function log(){
        $page = $this->request->get('page', 1);
        $pagesize = $this->request->get('pagesize', 10);
        $sum = ResellerLog::where('reseller_user_id', $this->auth->id)->where('site_id', $this->site_id)->sum('money');
        $count = ResellerLog::where('reseller_user_id', $this->auth->id)->where('site_id', $this->site_id)->count();
        $list = ResellerLog::alias('rl')
            ->join('user u', 'rl.user_id=u.id', 'left')
            ->field('rl.*,u.nickname,u.avatar')
            ->where('rl.reseller_user_id', $this->auth->id)
            ->where('rl.site_id', $this->site_id)
            ->order('rl.id', 'desc')
            ->page($page, $pagesize)
            ->select();
        foreach ($list as &$item){
            $item['avatar'] = cdnurl($item['avatar'], true);
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
        }
        $this->success('分销记录', ['sum'=>$sum, 'count'=>$count, 'list'=>$list]);
    }

    /**
     * 分销商订单记录
     */
    public function order_list()
    {
        $params = $this->request->get();

        $this->success('分销商订单记录', \addons\drama\model\ResellerOrder::getList($params));
    }

    /**
     * 分销商订单详情
     * @ApiParams   (name="id", type="integer", required=true, description="订单ID")
     * @ApiParams   (name="order_sn", type="string", required=true, description="订单号")
     */
    public function order_detail()
    {
        $params = $this->request->get();
        $this->success('订单详情', \addons\drama\model\ResellerOrder::detail($params));
    }

    /**
     * 分销商创建订单
     * @ApiMethod   (POST)
     * @ApiParams   (name="reseller_id", type="integer", required=true, description="分销商ID")
     * @ApiParams   (name="total_fee", type="string", required=true, description="金额")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     */
    public function recharge()
    {
        $params = $this->request->post();

        // 表单验证
        $this->dramaValidate($params, get_class(), 'recharge');

        $order = \addons\drama\model\ResellerOrder::recharge($params);

        $this->success('订单添加成功', $order);
    }


}
