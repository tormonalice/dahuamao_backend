<?php

namespace app\admin\controller;

use app\admin\library\Auth;
use app\admin\model\Admin;
use app\common\controller\Backend;
use fast\Http;
use fast\Random;
use think\Config;
use think\Db;
use think\Session;
use think\Validate;

/**
 * 站点
 *
 * @icon fa fa-circle-o
 */
class Sites extends Backend
{

    /**
     * Sites模型对象
     * @var \app\admin\model\Sites
     */
    protected $model = null;
    protected $adminModel = null;
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Sites;
        $this->adminModel = new Admin();
        $this->childrenAdminIds = $this->auth->getChildrenAdminIds($this->auth->isSuperAdmin());
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("isDefaultList", $this->model->getIsDefaultList());
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
                    ->with(['admin'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                if(isset($row->domain) && $row->domain){
                    $domain = $row->domain;
                }else{
                    $domain = $_SERVER['HTTP_HOST'];
                }
                $row->console = 1;
                
                $row->getRelation('admin')->visible(['username','nickname','email','mobile']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params['domain'] = '';
            if($params['is_default'] == 1 && $params['domain'] && $params['domain'] != $_SERVER['HTTP_HOST']){
                $this->error('默认站点必须绑定主域名！请确保当前域名为主域名！');
            }
            if($params['is_default'] != 1 && $params['domain'] && $params['domain'] == $_SERVER['HTTP_HOST']){
                $this->error('只有默认站点才能绑定主域名！');
            }
            if ($params) {
                Db::startTrans();
                try {
                    if (!Validate::is($params['password'], '\S{6,30}')) {
                        exception(__("Please input correct password"));
                    }
                    $params['salt'] = Random::alnum();
                    $params['password'] = md5(md5($params['password']) . $params['salt']);
                    $params['avatar'] = '/assets/img/logo.png'; //设置新管理员默认头像。
                    $result = $this->adminModel->validate('Admin.add')->allowField(true)->save($params);
                    if ($result === false) {
                        exception($this->adminModel->getError());
                    }
                    $group = ['2'];

                    //过滤不允许的组别,避免越权
                    $group = array_intersect($this->childrenGroupIds, $group);
                    if (!$group) {
                        exception(__('The parent group exceeds permission limit'));
                    }

                    $dataset = [];
                    foreach ($group as $value) {
                        $dataset[] = ['uid' => $this->adminModel->id, 'group_id' => $value];
                    }
                    model('AuthGroupAccess')->saveAll($dataset);
                    //添加站点
                    $params['sign'] = strtolower(Random::alnum(4));
                    while (true){
                        $site = $this->model->where('sign', $params['sign'])->find();
                        if(empty($site)){
                            break;
                        }
                        $params['sign'] = strtolower(Random::alnum(4));
                    }
                    $params['site_id'] = $this->adminModel->id;
                    $result = $this->model->allowField(true)->save($params);
                    if ($result === false) {
                        exception($this->model->getError());
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success();
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
        $admin = $this->adminModel->get(['id' => $ids]);
        $row = $this->model->get(['site_id' => $ids]);
        if (!$row || !$admin) {
            $this->error(__('No Results were found'));
        }
        if (!in_array($admin->id, $this->childrenAdminIds)) {
            $this->error(__('You have no permission'));
        }
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post("row/a");
            $params['domain'] = '';
            if($params['is_default'] == 1 && $params['domain'] && $params['domain'] != $_SERVER['HTTP_HOST']){
                $this->error('默认站点必须绑定主域名！请确保当前域名为主域名！');
            }
            if($params['is_default'] != 1 && $params['domain'] && $params['domain'] == $_SERVER['HTTP_HOST']){
                $this->error('只有默认站点才能绑定主域名！总后台请用主域名登录！');
            }
            if ($params) {
                Db::startTrans();
                try {
                    if ($params['password']) {
                        if (!Validate::is($params['password'], '\S{6,30}')) {
                            exception(__("Please input correct password"));
                        }
                        $params['salt'] = Random::alnum();
                        $params['password'] = md5(md5($params['password']) . $params['salt']);
                    } else {
                        unset($params['password'], $params['salt']);
                    }
                    //这里需要针对username和email做唯一验证
                    $adminValidate = \think\Loader::validate('Admin');
                    $adminValidate->rule([
                        'username' => 'require|regex:\w{3,30}|unique:admin,username,' . $admin->id,
                        'email'    => 'require|email|unique:admin,email,' . $admin->id,
                        'mobile'    => 'regex:1[3-9]\d{9}|unique:admin,mobile,' . $admin->id,
                        'password' => 'regex:\S{32}',
                    ]);
                    $result = $admin->validate('Admin.edit')->allowField(true)->save($params);
                    if ($result === false) {
                        exception($admin->getError());
                    }

                    // 先移除所有权限
                    model('AuthGroupAccess')->where('uid', $admin->id)->delete();

                    $group = [2];

                    // 过滤不允许的组别,避免越权
                    $group = array_intersect($this->childrenGroupIds, $group);
                    if (!$group) {
                        exception(__('The parent group exceeds permission limit'));
                    }

                    $dataset = [];
                    foreach ($group as $value) {
                        $dataset[] = ['uid' => $admin->id, 'group_id' => $value];
                    }
                    model('AuthGroupAccess')->saveAll($dataset);
                    //更新站点
                    $result = $row->allowField(true)->save($params);
                    if ($result === false) {
                        exception($row->getError());
                    }
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $grouplist = $this->auth->getGroups($admin['id']);
        $groupids = [];
        foreach ($grouplist as $k => $v) {
            $groupids[] = $v['id'];
        }
        $this->view->assign("row", $row);
        $this->view->assign("admin", $admin);
        $this->view->assign("groupids", $groupids);
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        if (!$this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ? $ids : $this->request->post("ids");
        if ($ids) {
            $ids = array_intersect($this->childrenAdminIds, array_filter(explode(',', $ids)));
            // 避免越权删除管理员
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList = $this->adminModel->where('id', 'in', $ids)->where('id', 'in', function ($query) use ($childrenGroupIds) {
                $query->name('auth_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
            })->select();
            if ($adminList) {
                $deleteIds = [];
                foreach ($adminList as $k => $v) {
                    $deleteIds[] = $v->id;
                }
                $deleteIds = array_values(array_diff($deleteIds, [$this->auth->id]));
                if ($deleteIds) {
                    Db::startTrans();
                    try {
                        $this->adminModel->destroy($deleteIds);
                        model('AuthGroupAccess')->where('uid', 'in', $deleteIds)->delete();
                        $this->model->destroy($deleteIds);
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    $this->success();
                }
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('You have no permission'));
    }

    public function to_site($site_id){
        $admin = Admin::get(['id' => $site_id]);
        if(empty($admin)){
            $this->error('站点不存在，请刷新后重试！', 'sites/index?ref=addtabs');
        }
        $admin->loginfailure = 0;
        $admin->logintime = time();
        $admin->loginip = request()->ip();
        $admin->token = Random::uuid();
        $admin->save();
        Session::set("admin", $admin->toArray());
        Session::set("admin.safecode", (new Auth())->getEncryptSafecode($admin));
        $this->redirect('index/index');
    }
}
