<?php

namespace app\admin\controller\drama;

use app\common\controller\Backend;
use app\admin\model\drama\Category as CategoryModel;
use fast\Tree;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * 分类管理
 */
class Category extends Backend
{
    /**
     * @var \app\admin\model\drama\Category
     */
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $model = null;
    protected $noNeedRight = ['selectpage', 'gettree'];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('app\admin\model\drama\Category');
        $this->assignconfig("type_list", $this->model->getTypeList());
    }

    /**
     * 选择分类
     */
    public function select()
    {
        if ($this->request->isAjax()) {
            $where = ['pid'=>0, $this->dataLimitField=>$this->auth->id];
            $type = $this->request->get('type', '');
            if($type != ''){
                $where['type'] = $type;
            }
            $list = $this->model->with('children.children.children')->where($where)->order('weigh desc, id asc')->select();
            $this->success('选择分类', null, $list);
        }
        return $this->view->fetch();
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            $where = ['pid'=>0, $this->dataLimitField=>$this->auth->id];
            $type = $this->request->get('type', '');
            if($type != ''){
                $where['type'] = $type;
            }
           $list = $this->model->with('children.children.children')->where($where)->order('weigh desc, id asc')->select();
           $this->success('自定义分类', null, $list);
        }
        return $this->view->fetch();
    }

    /**
     * 添加自定义分类
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
                $result = false;
                Db::startTrans();
                try {
                    $result = $this->model->allowField(true)->save($params);
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success('添加成功', null, $this->model->id);
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }

        if ($this->request->isPost()) {
            $params = $this->request->post();
            if ($params) {
                $params = json_decode($params['data'], true);
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    $result = $row->allowField(true)->save($params);
                    $result = true;
                    Db::commit();
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success();
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->assignconfig("row", $row);
        return $this->view->fetch();
    }

    public function update($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $params = $this->request->post();
        if($params) {
            $data = json_decode($params['data'], true);
            //递归处理分类数据
            $this->createOrUpdateCategory($data, $ids);
            $this->success();
        }
    }

    private function createOrUpdateCategory($data, $pid)
    {
        $parent = $this->model->get($pid);
        foreach($data as $k => $v) {
            $v['site_id'] = $parent['site_id'];
            $v['style'] = $parent['style'];
            $v['type'] = $parent['type'];
            $v['pid'] = $pid;
            if(!empty($v['id'])) {
                $row = $this->model->get($v['id']);
                if($row) {
                    if(isset($v['deleted']) && $v['deleted'] == 1) {
                        $row->delete();
                    }else {
                        $row->allowField(true)->save($v);
                    }
                }
            }else{
                $category = new \app\admin\model\drama\Category;
                $category->allowField(true)->save($v);
                $v['id'] = $category->id;
            }
            if(!empty($v['children'])) {
                $this->createOrUpdateCategory($v['children'], $v['id']);
            }
        }
    }


}
