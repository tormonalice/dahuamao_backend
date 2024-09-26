<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\controller\drama;

use app\common\controller\Backend;
use think\Db;
use think\exception\DbException;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 分销商
 *
 * @icon fa fa-circle-o
 */
class Mgg extends Backend
{
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['sync', 'select'];

    /**
     * mgg模型对象
     * @var \app\admin\model\drama\mgg
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\drama\Mgg;
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $expire_type = isset($params['expire_type']) && $params['expire_type'] ? $params['expire_type'] : 'day';
        if($expire_type == 'year'){
            $params['expire'] = $params['expire'] * 365 * 86400;
        }elseif($expire_type == 'month'){
            $params['expire'] = $params['expire'] * 30 * 86400;
        }else{
            $params['expire'] = $params['expire'] * 1 * 86400;
        }
        if(isset($params['expire_type'])){
            unset($params['expire_type']);
        }
        $params = $this->preExcludeFields($params);
        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    /**
     * 编辑
     *
     * @param $ids
     * @return string
     * @throws DbException
     * @throws \think\Exception
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            if($row['expire'] > 0 && $row['expire'] % (86400 * 365) == 0){
                $row['expire_type'] = 'year';
                $row['expire'] = intval($row['expire'] / (86400 * 365));
            }elseif($row['expire'] > 0 && $row['expire'] % (86400 * 30) == 0){
                $row['expire_type'] = 'month';
                $row['expire'] = intval($row['expire'] / (86400 * 30));
            }else{
                $row['expire_type'] = $row['expire'] > 0 ? 'day' : '';
                $row['expire'] = intval($row['expire'] / (86400 * 1));
            }
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        /*
        $expire_type = isset($params['expire_type']) && $params['expire_type'] ? $params['expire_type'] : 'day';
        if($expire_type == 'year'){
            $params['expire'] = $params['expire'] * 365 * 86400;
        }elseif($expire_type == 'month'){
            $params['expire'] = $params['expire'] * 30 * 86400;
        }else{
            $params['expire'] = $params['expire'] * 1 * 86400;
        }
        if(isset($params['expire_type'])){
            unset($params['expire_type']);
        }
        */

        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }


    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    public function sync(){
        $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "mgg.php";
        if (is_file($testdata_file)) {
            $datas = include $testdata_file;
            Db::startTrans();
            foreach ($datas as $templine) {
                $templine = str_ireplace('__PREFIX__', config('database.prefix'), $templine);
                $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                try {
                    Db::execute($templine);
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error('测试数据导入错误：'.$e->getMessage());
                }
            }
            Db::commit();
            $this->success('测试数据导入成功！');
        }else{
            $this->error('数据文件不存在！');
        }

    }

    /**
     * 选择
     */
    public function select()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $total = $this->model
                ->where($where)
                ->order($sort, $order)
                ->field('id, name')
                ->count();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->field('id, name')
                ->limit($offset, $limit)
                ->select();
            $result = array("total" => $total, "rows" => $list);

            $this->success('选择分销商套餐', null, $result);
        }
    }

}
