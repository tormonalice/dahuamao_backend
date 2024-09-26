<?php

namespace app\admin\controller\drama;

use app\admin\model\drama\Video;
use app\common\controller\Backend;
use app\common\model\drama\Point;

/**
 * 搜索统计
 *
 * @icon fa fa-circle-o
 *
 * point_type类型
 * 1=首页点击
 * 2=追剧页点击
 * 3=搜索展示
 * 4=搜索点击
 * 5=底部tab
 * 6=开通会员
 * 7=去充值
 *
 * item_id底部tab
 * 1=首页
 * 2=追剧
 * 3=推荐
 * 4=我的
 *
 */
class Sou extends Backend
{

    /**
     * VideoView模型对象
     * @var \app\common\model\drama\Point
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\drama\Point;

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

            //$offset=input('offset');
            //$limit=input('limit');
            // 获取搜索框的值
            $filter=input('filter');
            $where2 = [];
            $where3 = [];
            if($filter){
                $filter=urldecode($filter);
                $filter=json_decode($filter,TRUE);
                foreach($filter as $k=>$v){
                    if($k != 'starttime' && $k != 'endtime'){
                        $where2[$k]=['like',"%{$v}%"];
                    }
                    if($k == 'starttime'){
                        $where3['createtime'] = ['egt',strtotime($v)];
                    }
                    if($k == 'endtime'){
                        $where3['createtime'] = ['elt',strtotime($v)];
                    }
                }
            }

            $list = Point::where($where2)
                ->where(['site_id'=>$this->auth->id,'point_type'=>3])
                ->field('content')
                ->group('content')
                ->paginate($limit)
                ->each(function($item)use($where3){

                    $item->total_user = Point::where(['content'=>$item->content,'point_type'=>3,'user_type'=>2])->where($where3)->group('user_id')->count();
                    $item->total_user_view = Point::where(['content'=>$item->content,'point_type'=>3,'user_type'=>2])->where($where3)->count();
                    $item->total_visitor_view = Point::where(['content'=>$item->content,'point_type'=>3,'user_type'=>1])->where($where3)->count();
                    $item->total_view = $item->total_user_view + $item->total_visitor_view;

                });

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
