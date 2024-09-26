<?php

namespace app\admin\controller\drama;

use addons\drama\library\Export;
use app\common\controller\Backend;
use Exception;
use fast\Random;
use think\Db;

/**
 * 卡密
 *
 * @icon fa fa-circle-o
 */
class Cryptocard extends Backend
{

    /**
     * Cryptocard模型对象
     * @var \app\admin\model\drama\Cryptocard
     */
    protected $model = null;
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['selectSearch'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\drama\Cryptocard;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax())
        {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }
            $searchWhere = $this->request->request('searchWhere');

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->where(function ($query) use ($searchWhere) {
                    if ($searchWhere) {
                        $table_name = (new \app\admin\model\drama\Cryptocard())->getQuery()->getTable();
                        $query = $query->table($table_name)->whereOr($table_name.'.id', '=', $searchWhere)
                            ->whereOr($table_name.'.name', 'like', "%$searchWhere%");
                    }
                    return $query;
                })
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->where($where)
                ->where(function ($query) use ($searchWhere) {
                    if ($searchWhere) {
                        $table_name = (new \app\admin\model\drama\Cryptocard())->getQuery()->getTable();
                        $query = $query->table($table_name)->whereOr($table_name.'.id', '=', $searchWhere)
                            ->whereOr($table_name.'.name', 'like', "%$searchWhere%");
                    }
                    return $query;
                })
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {
                $row->item_title = $this->getItem($row);
                $row->user_cryptocard = $this->getUserCryptocard($row);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            return $this->success('卡密', null, $result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post();
            if ($params) {
                $params = json_decode($params['data'], true);
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $params['status'] = 0;
                $usetimeArray = explode(' - ', $params['usetime']);
                $params['usetimestart'] = strtotime($usetimeArray[0]);
                $params['usetimeend'] = strtotime($usetimeArray[1]);
                $stock = $params['stock'];
                $pwd_type = $params['pwd_type'];
                $pwd_len = $params['pwd_len'];
                unset($params['stock'], $params['pwd_type'], $params['pwd_len']);
                $success = 0;
                for($i=0;$i<$stock;++$i){
                    $params['pwd'] = $this->getStrRandom($pwd_type, $pwd_len);
                    $result = false;
                    try {
                        $result = $this->model->allowField(true)->create($params);
                        if ($result !== false) {
                            ++$success;
                        }
                    } catch (Exception $e) {}
                }

                if ($success > 0) {
                    $this->success('共生成成功'.$success.'个卡密！');
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }


        return $this->view->fetch();
    }

    /**
     * 回收站
     */
    public function recyclebin()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->onlyTrashed()
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->model
                ->onlyTrashed()
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    private function getItem($data)
    {
        if($data['type'] == 'vip'){
            return \app\admin\model\drama\Vip::where('id', $data['item_id'])->value('title');
        }elseif($data['type'] == 'reseller'){
            return \app\admin\model\drama\Reseller::where('id', $data['item_id'])->value('name');
        }elseif($data['type'] == 'usable'){
            return \app\admin\model\drama\Usable::where('id', $data['item_id'])->value('title');
        }
        return null;
    }

    private function getUserCryptocard($data){
        if($data['status'] == 1){
            $item = Db::name('drama_user_cryptocard')
                ->alias('uc')
                ->join('user u', 'u.id=uc.user_id')
                ->where('uc.cryptocard_id', $data['id'])
                ->field('u.nickname,u.avatar,u.mobile,uc.*')
                ->find();
            if($item){
                if($item['type'] == 'vip'){
                    $item['type_text'] = 'VIP套餐订单';
                }elseif($item['type'] == 'reseller'){
                    $item['type_text'] = '分销商套餐订单';
                }elseif($item['type'] == 'usable'){
                    $item['type_text'] = '剧场积分套餐订单';
                }
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
                return $item;
            }
        }
        return null;
    }

    private function getStrRandom($type, $len){
        if($type == 'alpha'){
            $str = Random::alpha($len);
        }elseif($type == 'numeric'){
            $str = Random::numeric($len);
        }elseif($type == 'nozero'){
            $str = Random::nozero($len);
        }else{
            $str = Random::alnum($len);
        }
        return $str;
    }

    /**
     * 选择
     */
    public function selectSearch($type='all')
    {
        if ($this->request->isAjax()) {
            $list = [];
            if($type == 'vip' || $type == 'all'){
                $list1 = \app\admin\model\drama\Vip::where(['status'=>'1','site_id'=>$this->auth->id])
                    ->order('weigh desc, id asc')
                    ->field('id, title')
                    ->select();
                if($list1){
                    $list = array_merge($list, $list1);
                }
            }
            if($type == 'reseller' || $type == 'all'){
                $list2 = \app\admin\model\drama\Reseller::where(['status'=>'normal','site_id'=>$this->auth->id])
                    ->whereNull('deletetime')
                    ->order('weigh desc, id asc')
                    ->field('id, name as title')
                    ->select();
                if($list2){
                    $list = array_merge($list, $list2);
                }
            }
            if($type == 'usable' || $type == 'all'){
                $list3 = \app\admin\model\drama\Usable::where(['status'=>'1','site_id'=>$this->auth->id])
                    ->order('weigh desc, id asc')
                    ->field('id, title')
                    ->select();
                if($list3){
                    $list = array_merge($list, $list3);
                }
            }

            $this->success('选择套餐', null, $list);
        }
    }

    public function export(){
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $expCellName = [
            'id' => 'Id',
            'type_text' => '卡密类型',
            'item_title' => '卡密套餐',
            'name' => '卡密名称',
            'pwd' => '卡密兑换码',
            'remark' => '卡密备注',
            'status_text' => '卡密状态',
            'usetime' => '卡密有效期',
        ];
        $export = new Export();
        $spreadsheet = null;
        $sheet = null;

        $total = $this->model->where($where)->order($sort, $order)->count();
        $current_total = 0;     // 当前已循环条数
        $page_size = 1000;
        $total_page = intval(ceil($total / $page_size));
        $countTotal = 0;

        if ($total == 0) {
            $this->error('导出数据为空');
        }

        for ($i = 0; $i < $total_page; $i++) {
            $page = $i + 1;
            $is_last_page = ($page == $total_page) ? true : false;

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->limit(($i * $page_size), $page_size)
                ->select();
            foreach ($list as $row) {
                ++$countTotal;
                $row->item_title = $this->getItem($row);
            }

            $newList = collection($list)->toArray();

            if ($is_last_page) {
                $newList[] = [
                    'id' => "卡密总数：" . $countTotal
                ];
            }

            $current_total += count($newList);     // 当前循环总条数

            $export->exportExcel('卡密列表-' . date('Y-m-d H:i:s'), $expCellName, $newList, $spreadsheet, $sheet, [
                'page' => $page,
                'page_size' => $page_size,      // 如果传了 current_total 则 page_size 就不用了
                'current_total' => $current_total,      // page_size 是 order 的，但是 newList 其实是 order_item 的
                'is_last_page' => $is_last_page
            ]);
        }
    }

}
