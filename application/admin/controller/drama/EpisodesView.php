<?php

namespace app\admin\controller\drama;

use app\common\controller\Backend;
use app\admin\model\drama\VideoEpisodes;
use app\common\model\drama\ViewLog;

/**
 * 剧集观看统计
 *
 * @icon fa fa-circle-o
 */
class EpisodesView extends Backend
{

    /**
     * EpisodesView模型对象
     * @var \app\common\model\drama\EpisodesView
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\drama\EpisodesView;

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
                    ->with(['episodes','video'])
                    ->where($where)
                    ->where('episodes_view.site_id',$this->auth->id)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('episodes')->visible(['name']);
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

            $list = VideoEpisodes::alias('ve')
                ->join('drama_video v','ve.vid = v.id and v.deletetime is null','inner')
                ->where($where2)
                ->where('ve.site_id',$this->auth->id)
                ->field('ve.id,v.title,ve.name')
                ->paginate($limit)
                ->each(function($item)use($where3){
                    $item->total_user = ViewLog::where(['episodes_id'=>$item->id,'type'=>2])->where($where3)->group('user_id')->count();
                    $item->total_user_view = ViewLog::where(['episodes_id'=>$item->id,'type'=>2])->where($where3)->count();
                    $item->total_visitor_view = ViewLog::where(['episodes_id'=>$item->id,'type'=>1])->where($where3)->count();
                    $item->total_view = ViewLog::where(['episodes_id'=>$item->id])->where($where3)->count();
                });

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
