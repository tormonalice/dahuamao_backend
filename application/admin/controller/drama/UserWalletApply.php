<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\controller\drama;

use app\common\controller\Backend;
use think\Db;
use addons\drama\model\UserWalletApply as WithDraw;

/**
 * 用户提现
 *
 * @icon fa fa-circle-o
 */
class UserWalletApply extends Backend
{
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['getType'];

    /**
     * UserWalletApply模型对象
     * @var \app\admin\model\drama\UserWalletApply
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\drama\UserWalletApply;
        $this->view->assign("getTypeList", $this->model->getApplyTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->assignconfig('typeList', $this->model->getApplyTypeList());
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
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            $nobuildfields = ['user_nickname', 'user_mobile'];
            list($where, $sort, $order, $offset, $limit) = $this->custombuildparams(null, $nobuildfields);

            $total = $this->buildSearch()
                ->where($where)
                ->order($sort, $order)
                ->count();

            $list = $this->buildSearch()
                ->with('user')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return $this->success('操作成功', null, $result);
        }
        return $this->view->fetch();
    }


    // 获取要查询的提现类型
    public function getType()
    {
        $apply_type = $this->model->getApplyTypeList();
        $status = $this->model->getStatusList();

        $result = [
            'apply_type' => $apply_type,
            'status' => $status,
        ];

        $data = [];
        foreach ($result as $key => $list) {
            $data[$key][] = ['name' => '全部', 'type' => 'all'];

            foreach ($list as $k => $v) {
                $data[$key][] = [
                    'name' => $v,
                    'type' => $k
                ];
            }
        }

        return $this->success('操作成功', null, $data);
    }

    public function handle($ids)
    {
        $successCount = 0;
        $failedCount = 0;
        $ids = explode(',', $ids);
        $applyList = $this->model->where('id', 'in', $ids)->select();
        if (!$applyList) {
            $this->error('未找到该提现申请');
        }
        $operate = $this->request->post('operate');
        foreach ($applyList as $apply) {
            Db::startTrans();
            try {
                switch ($operate) {
                    case '1':
                        WithDraw::handleAgree($apply);
                        $apply->status === 1 ? $successCount++ : $failedCount++;
                        break;
                    case '2':
                        WithDraw::handleWithdraw($apply);
                        $apply->status === 2 ? $successCount++ : $failedCount++;
                        break;
                    case '3':
                        WithDraw::handleAgree($apply);
                        WithDraw::handleWithdraw($apply);
                        $apply->status === 2 ? $successCount++ : $failedCount++;
                        break;
                    case '-1':
                        $rejectInfo = $this->request->post('rejectInfo');
                        if (!$rejectInfo) {
                            throw new \Exception('请输入拒绝原因');
                        }
                        WithDraw::handleReject($apply, $rejectInfo);
                        $apply->status === -1 ? $successCount++ : $failedCount++;;
                        break;
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                WithDraw::handleLog($apply, '失败: ' . $e->getMessage());
                $failedCount++;
                $lastErrorMessage = $e->getMessage();
            }
        }
        if (count($ids) === 1) {
            if ($successCount) $this->success('操作成功');
            if ($failedCount) $this->error($lastErrorMessage);
        } else {
            $this->success('成功: ' . $successCount . '笔' . ' | 失败: ' . $failedCount . '笔');
        }
    }

    public function log($id)
    {
        $apply = $this->model->get($id);
        if (!$apply) {
            $this->error('未找到该提现日志');
        }
        $applyLog = $apply->log;
        if ($applyLog) {
            foreach ($applyLog as &$log) {
                $log['oper'] = \addons\drama\library\Oper::get($log['oper_type'], $log['oper_id']);
            }
        }
        $this->success('提现日志', null, $applyLog);
    }

    /**
     * 提现搜索
     *
     * @return object
     */
    public function buildSearch()
    {
        $filter = $this->request->get("filter", '');
        $filter = (array)json_decode($filter, true);
        $filter = $filter ? $filter : [];

        $user_nickname = isset($filter['user_nickname']) ? $filter['user_nickname'] : '';
        $user_mobile = isset($filter['user_mobile']) ? $filter['user_mobile'] : '';

        // 当前表名
        $tableName = $this->model->getQuery()->getTable();

        $applys = $this->model;

        // 购买人查询
        if ($user_nickname || $user_mobile) {
            $applys = $applys->whereExists(function ($query) use ($user_nickname, $user_mobile, $tableName) {
                $userTableName = (new \app\admin\model\User())->getQuery()->getTable();
                $query = $query->table($userTableName)->where($userTableName . '.id=' . $tableName . '.user_id');

                if ($user_nickname) {
                    $query = $query->where('nickname', 'like', "%{$user_nickname}%");
                }

                if ($user_mobile) {
                    $query = $query->where('mobile', 'like', "%{$user_mobile}%");
                }

                return $query;
            });
        }

        return $applys;
    }

    /**
     * 可自定义组合的条件 生成查询所需要的条件,排序方式
     * @param mixed   $searchfields   快速查询的字段
     * @param mixed   $nobuildfields   不参与buildParams 的字段
     * @param boolean $relationSearch 是否关联查询
     * @return array
     */
    protected function custombuildparams($searchfields = null, $nobuildfields = [], $relationSearch = null)
    {
        $searchfields = is_null($searchfields) ? $this->searchFields : $searchfields;
        $relationSearch = is_null($relationSearch) ? $this->relationSearch : $relationSearch;
        $search = $this->request->get("search", '');
        $filter = $this->request->get("filter", '');
        $op = $this->request->get("op", '', 'trim');
        $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
        $order = $this->request->get("order", "DESC");
        $offset = $this->request->get("offset", 0);
        $limit = $this->request->get("limit", 0);
        $filter = (array)json_decode($filter, true);
        $op = (array)json_decode($op, true);
        $filter = $filter ? $this->filterParams($filter, $nobuildfields) : [];     // 过滤掉不参与 buildParams 的参数
        $where = [];
        $tableName = '';
        if ($relationSearch) {
            if (!empty($this->model)) {
                $name = \think\Loader::parseName(basename(str_replace('\\', '/', get_class($this->model))));
                $name = $this->model->getTable();
                $tableName = $name . '.';
            }
            $sortArr = explode(',', $sort);
            foreach ($sortArr as $index => &$item) {
                $item = stripos($item, ".") === false ? $tableName . trim($item) : $item;
            }
            unset($item);
            $sort = implode(',', $sortArr);
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $where[] = [$tableName . $this->dataLimitField, 'in', $adminIds];
        }
        if ($search) {
            $searcharr = is_array($searchfields) ? $searchfields : explode(',', $searchfields);
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $where[] = [implode("|", $searcharr), "LIKE", "%{$search}%"];
        }
        foreach ($filter as $k => $v) {
            $sym = isset($op[$k]) ? $op[$k] : '=';
            if (stripos($k, ".") === false) {
                $k = $tableName . $k;
            }
            $v = !is_array($v) ? trim($v) : $v;
            $sym = strtoupper(isset($op[$k]) ? $op[$k] : $sym);
            switch ($sym) {
                case '=':
                case '<>':
                    $where[] = [$k, $sym, (string)$v];
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                case 'LIKE %...%':
                case 'NOT LIKE %...%':
                    $where[] = [$k, trim(str_replace('%...%', '', $sym)), "%{$v}%"];
                    break;
                case '>':
                case '>=':
                case '<':
                case '<=':
                    $where[] = [$k, $sym, intval($v)];
                    break;
                case 'FINDIN':
                case 'FINDINSET':
                case 'FIND_IN_SET':
                    $where[] = "FIND_IN_SET('{$v}', " . ($relationSearch ? $k : '`' . str_replace('.', '`.`', $k) . '`') . ")";
                    break;
                case 'IN':
                case 'IN(...)':
                case 'NOT IN':
                case 'NOT IN(...)':
                    $where[] = [$k, str_replace('(...)', '', $sym), is_array($v) ? $v : explode(',', $v)];
                    break;
                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'BETWEEN' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'BETWEEN' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, $sym, $arr];
                    break;
                case 'RANGE':
                case 'NOT RANGE':
                    $v = str_replace(' - ', ',', $v);
                    $arr = array_slice(explode(',', $v), 0, 2);
                    if (stripos($v, ',') === false || !array_filter($arr)) {
                        continue 2;
                    }
                    //当出现一边为空时改变操作符
                    if ($arr[0] === '') {
                        $sym = $sym == 'RANGE' ? '<=' : '>';
                        $arr = $arr[1];
                    } elseif ($arr[1] === '') {
                        $sym = $sym == 'RANGE' ? '>=' : '<';
                        $arr = $arr[0];
                    }
                    $where[] = [$k, str_replace('RANGE', 'BETWEEN', $sym) . ' time', $arr];
                    break;
                case 'LIKE':
                case 'LIKE %...%':
                    $where[] = [$k, 'LIKE', "%{$v}%"];
                    break;
                case 'NULL':
                case 'IS NULL':
                case 'NOT NULL':
                case 'IS NOT NULL':
                    $where[] = [$k, strtolower(str_replace('IS ', '', $sym))];
                    break;
                default:
                    break;
            }
        }
        $where = function ($query) use ($where) {
            foreach ($where as $k => $v) {
                if (is_array($v)) {
                    call_user_func_array([$query, 'where'], $v);
                } else {
                    $query->where($v);
                }
            }
        };
        return [$where, $sort, $order, $offset, $limit];
    }



    /**
     * 过滤原始的不能用buildParams 的条件
     */
    public function filterParams($filter, $nobuildfields = []) {
        if ($nobuildfields) {
            foreach ($filter as $k => $f) {
                if (in_array($k, $nobuildfields)) {
                    unset($filter[$k]);
                }
            }
        }

        return $filter;
    }

}
