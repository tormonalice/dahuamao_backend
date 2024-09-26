<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;


use think\Exception;
use addons\drama\model\VideoImages as VideoImagesModel;

/**
 * 短剧壁纸
 * Class Share
 * @package addons\drama\controller
 */
class VideoImages extends Base
{

    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 壁纸列表
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $page = $this->request->get('page', 1);
        $pagesize = $this->request->get('pagesize', 10);
        $list = VideoImagesModel::with('video')
            ->orderRaw('views desc, downloads desc, id desc')
            ->page($page, $pagesize)
            ->select();
        foreach ($list as &$item){
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
        }
        $this->success('壁纸列表', $list);
    }

    /**
     * 增加浏览/下载次数
     * @ApiParams   (name="id", type="integer", required=false, description="壁纸ID")
     * @ApiParams   (name="type", type="string", required=false, description="views浏览次数，downloads下载次数")
     * @throws Exception
     */
    public function setInc(){
        $id = $this->request->get('id');
        $type = $this->request->get('type', 'views');
        VideoImagesModel::where('id', $id)->setInc($type);
        $this->success('');
    }

}
