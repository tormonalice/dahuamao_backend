<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\controller\drama;

use app\common\controller\Backend;

/**
 * 分佣记录
 *
 * @icon fa fa-circle-o
 */
class ResellerLog extends Backend
{
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';

    /**
     * ResellerLog模型对象
     * @var \app\admin\model\drama\ResellerLog
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\drama\ResellerLog;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("orderTypeList", $this->model->getOrderTypeList());
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
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['user', 'reseller'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->getRelation('user')->visible(['nickname']);
                $row->getRelation('reseller')->visible(['nickname']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
