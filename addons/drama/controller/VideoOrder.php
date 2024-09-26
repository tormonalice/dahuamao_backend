<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\VideoOrder as VideoOrderModel;
use addons\drama\model\Video;
use addons\drama\model\VideoEpisodes;
use think\Exception;

/**
 * 短剧订单
 * Class Share
 * @package addons\drama\controller
 */
class VideoOrder extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    /**
     * 支付订单
     * @ApiMethod   (POST)
     * @ApiParams   (name="vid", type="integer", required=false, description="短剧ID")
     * @ApiParams   (name="episode_id", type="integer", required=false, description="剧集ID")
     * @ApiParams   (name="type", type="string", required=false, description="episode单集购买，video整集购买")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,App=App")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function add(){
        $vid = $this->request->post('vid');
        $episode_id = $this->request->post('episode_id');
        $type = $this->request->post('type', 'episode');
        $platform = $this->request->param('platform', 'H5');
        if($type == 'episode'){
            $episodes = VideoEpisodes::where('id', $episode_id)->where('vid', $vid)->find();
            if(empty($episodes)){
                $this->error('短剧不存在，请刷新后重试！');
            }
            try {
                VideoOrderModel::addVideoEpisodes($platform, $episodes);
            }catch (Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('成功');
        }else{
            $video = Video::where('id', $vid)->find();
            if(empty($video)){
                $this->error('短剧不存在，请刷新后重试！');
            }
            try {
                VideoOrderModel::addVideos($platform, $video);
            }catch (Exception $e){
                $this->error($e->getMessage());
            }
            $this->success('成功');
        }
    }

    /**
     * 订单列表
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index(){
        $page = $this->request->get('page', 1);
        $pagesize = $this->request->get('pagesize', 10);
        $user = $this->auth->getUser();
        $list = VideoOrderModel::with(['video' => function ($query) {
            $query->removeOption('soft_delete');
        }, 'episode' => function ($query) {
            $query->removeOption('soft_delete');
        }])->where([
            'user_id' => $user->id
        ])->order('createtime', 'DESC')->page($page, $pagesize)->select();
        foreach ($list as &$data){
            if(isset($data['createtime'])){
                $data['createtime'] = date('Y-m-d H:i:s', $data['createtime']);
            }
            if(isset($data['video']) && $data['video']){
                $video = $data['video']->toArray();
                $video['image'] = $video['image']?cdnurl($video['image'], true):'';
                $video['views'] = $video['views'] + $video['fake_views'];
                $video['likes'] = $video['likes'] + $video['fake_likes'];
                $video['shares'] = $video['shares'] + $video['fake_shares'];
                $video['favorites'] = $video['favorites'] + $video['fake_favorites'];
                unset($data['video'], $video['content'],$video['fake_favorites'],$video['fake_shares'],$video['fake_views'],$video['fake_likes']);
                $data['video'] = $video;
            }
            if(isset($data['episode']) && $data['episode']){
                $episode = $data['episode']->toArray();
                $episode['image'] = $episode['image']?cdnurl($episode['image'], true):'';
                $episode['video'] = $episode['video']?cdnurl($episode['video'], true):'';
                $episode['views'] = $episode['views'] + $episode['fake_views'];
                $episode['likes'] = $episode['likes'] + $episode['fake_likes'];
                $episode['shares'] = $episode['shares'] + $episode['fake_shares'];
                $episode['favorites'] = $episode['favorites'] + $episode['fake_favorites'];
                unset($data['episode'], $episode['fake_favorites'],$episode['fake_shares'],$episode['fake_views'],$episode['fake_likes']);
                $data['episode'] = $episode;
            }
        }
        $this->success('', $list);
    }

    /**
     * 删除订单
     * @ApiMethod   (POST)
     * @ApiParams   (name="ids", type="string", required=false, description="订单ID（英文下逗号,分隔）")
     * @throws \think\exception\DbException
     */
    public function del(){
        $ids = $this->request->post('ids');
        $user = $this->auth->getUser();
        $order_ids = explode(',', $ids);
        foreach ($order_ids as $g) {
            $video_order = VideoOrderModel::get(['id' => $g, 'user_id' => $user->id]);
            if($video_order){
                $video_order->delete();
            }
        }
        $this->success('删除成功！');
    }
}
