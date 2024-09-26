<?php

namespace app\admin\controller\drama;

use app\admin\model\drama\Video;
use app\common\controller\Backend;
use app\common\model\drama\Point;

/**
 * 剧目观看统计
 *
 * @icon fa fa-circle-o
 *
 *
 * point_type
 * 1=首页点击
 * 2=追剧界面点击
 * 4=搜索界面点击
 *
 */
class VideoView extends Backend
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

            /*
            $list = $this->model
                    ->with(['video'])
                    ->where($where)
                    ->where('video_view.site_id',$this->auth->id)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('video')->visible(['title']);
            }
            */

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

            $list = Video::where($where2)
                ->where('site_id',$this->auth->id)
                ->field('id,title')
                ->paginate($limit)
                ->each(function($item)use($where3){

                    $item->index_total_user = Point::where(['item_id'=>$item->id,'point_type'=>1,'user_type'=>2])->where($where3)->group('user_id')->count();
                    $item->index_total_user_view = Point::where(['item_id'=>$item->id,'point_type'=>1,'user_type'=>2])->where($where3)->count();
                    $item->index_total_visitor_view = Point::where(['item_id'=>$item->id,'point_type'=>1,'user_type'=>1])->where($where3)->count();
                    $item->index_total_view = $item->index_total_user_view + $item->index_total_visitor_view;

                    $item->zhui_total_user = Point::where(['item_id'=>$item->id,'point_type'=>2,'user_type'=>2])->where($where3)->group('user_id')->count();
                    $item->zhui_total_user_view = Point::where(['item_id'=>$item->id,'point_type'=>2,'user_type'=>2])->where($where3)->count();
                    $item->zhui_total_visitor_view = Point::where(['item_id'=>$item->id,'point_type'=>2,'user_type'=>1])->where($where3)->count();
                    $item->zhui_total_view = $item->zhui_total_user_view + $item->zhui_total_visitor_view;

                    $item->sou_total_user = Point::where(['item_id'=>$item->id,'point_type'=>4,'user_type'=>2])->where($where3)->group('user_id')->count();
                    $item->sou_total_user_view = Point::where(['item_id'=>$item->id,'point_type'=>4,'user_type'=>2])->where($where3)->count();
                    $item->sou_total_visitor_view = Point::where(['item_id'=>$item->id,'point_type'=>4,'user_type'=>1])->where($where3)->count();
                    $item->sou_total_view = $item->sou_total_user_view + $item->sou_total_visitor_view;

                });

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
