<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\controller\drama;

use app\common\controller\Backend;
use think\Db;

/**
 * 区块管理
 *
 * @icon fa fa-circle-o
 */
class Block extends Backend
{
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['sync'];

    /**
     * Block模型对象
     * @var \app\admin\model\drama\Block
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\drama\Block;
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("parsetplList", $this->model->getParsetplList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("nameList", $this->model->getNameList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    public function sync(){
        $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "block.php";
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

}
