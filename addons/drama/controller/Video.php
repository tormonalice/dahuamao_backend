<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\library\Wechat as Wc;
use addons\drama\model\Config;
use addons\drama\model\Usable;
use addons\drama\model\UsableOrder;
use addons\drama\model\User;
use addons\drama\model\UserOauth;
use addons\drama\model\Video as VideoModel;
use addons\drama\model\VideoEpisodes;
use addons\drama\model\VideoFavorite;
use addons\drama\model\VideoLog;
use addons\drama\model\VideoPerformer;
use addons\drama\model\VideoOrder;
use app\common\model\drama\EpisodesView;
use app\common\model\drama\VideoView;
use app\common\model\drama\ViewLog;
use EasyWeChat\Kernel\Exceptions\HttpException;
use fast\Http;
use think\Db;
use think\Exception;


use app\common\controller\Api;

use think\Cache;

use think\Lang;

/**
 * 短剧
 * Class Share
 * @package addons\drama\controller
 */
class Video extends Api
{

    protected $noNeedLogin = ['index', 'detail', 'recommend','addlog','test','wxtuijian','bindwx'];
    protected $noNeedRight = ['*'];

    /**
     * 短剧列表
     * @ApiParams   (name="type", type="string", required=false, description="类型：recommend推荐，hot：热门，score：好评，new：最新，free：免费")
     * @ApiParams   (name="tag", type="string", required=false, description="标签")
     * @ApiParams   (name="search", type="string", required=false, description="搜索")
     * @ApiParams   (name="category_id", type="integer", required=false, description="分类ID")
     * @ApiParams   (name="area_id", type="integer", required=false, description="地区ID")
     * @ApiParams   (name="year_id", type="integer", required=false, description="年份ID")
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     */
    public function index(){
        
        $filePath = ROOT_PATH . 'public/fubi/fubi03.txt';
        if (file_exists($filePath)) {
        $file = fopen($filePath, 'r'); // 打开文件
        $fileSize = filesize($filePath); // 获取文件大小
        $content = fread($file, $fileSize); // 读取文件内容
        fclose($file); // 关闭文件
 
        // 返回响应
        return response($content, 200, ['Content-Type' => 'application/json; charset=utf-8'])->contentType('application/json; charset=utf-8');
        } else {
            return '文件不存在';
        }
    }

    /**
     * 短剧详情
     * @ApiParams   (name="id", type="integer", required=false, description="短剧ID")
     * @throws \think\exception\DbException
     */
    public function detail(){
        $id = $this->request->get('id');
        
        if ($id == 9){
            $file_name = 'public/fubi/fubi05.txt';
        
        }
        elseif($id == 8){
            $file_name = 'public/fubi/fubi06.txt';
        }
        elseif($id == 7){
            $file_name = 'public/fubi/fubi07.txt';
        }
        

        //$this->success('短剧详情', $info);
        
        $filePath = ROOT_PATH . $file_name;
        if (file_exists($filePath)) {
        $file = fopen($filePath, 'r'); // 打开文件
        $fileSize = filesize($filePath); // 获取文件大小
        $content = fread($file, $fileSize); // 读取文件内容
        fclose($file); // 关闭文件
 
        // 返回响应
        return response($content, 200, ['Content-Type' => 'application/json; charset=utf-8'])->contentType('application/json; charset=utf-8');
        } else {
            return '文件不存在';
        }
    }

    /**
     * 微信小程序媒资短剧详情
     * @ApiParams   (name="id", type="integer", required=false, description="短剧ID")
     * @throws \think\exception\DbException
     */
    public function wxdetail(){

        $id = $this->request->get('id');
        $platform = $this->request->param('platform',1);
        $info = \addons\drama\model\Video::get($id);
        if(empty($info)){
            $this->error('短剧不存在，请刷新后重试！');
        }
        $info['createtime'] = date('Y-m-d H:i:s', $info['createtime']);
        $info['updatetime'] = date('Y-m-d H:i:s', $info['updatetime']);
        $info['views'] = $info['views'] + $info['fake_views'];
        $info['likes'] = $info['likes'] + $info['fake_likes'];
        $info['shares'] = $info['shares'] + $info['fake_shares'];
        $info['favorites'] = $info['favorites'] + $info['fake_favorites'];
        unset($info['content'],$info['fake_favorites'],$info['fake_shares'],$info['fake_views'],$info['fake_likes']);
        $info['is_favorite'] = 0;
        $info['episode_id'] = 0;
        $info['view_time'] = 0;
        $user_id = $this->auth->id;
        if($user_id){
            $video_log = VideoLog::where(['user_id'=>$user_id, 'vid'=>$info['id'], 'type'=>'favorite',
                'site_id'=>$this->site_id])->find();
            if($video_log){
                $info['is_favorite'] = 1;
            }
            $video_log = VideoLog::where(['user_id'=>$user_id, 'vid'=>$info['id'], 'type'=>'log',
                'site_id'=>$this->site_id])
                ->order('createtime', 'desc')->find();
            if($video_log){
                $info['episode_id'] = $video_log['episode_id'];
                $info['view_time'] = $video_log['view_time'];
            }
        }
        $inf = base64_decode('Y2hlY2tfaG9zdA==');
        $this->$inf();
        $performer = VideoPerformer::where(['vid'=>$id, 'site_id'=>$this->site_id])->order('weigh desc, id asc')->select();
        $info['performer_list'] = $performer;
        $episodes = VideoEpisodes::where(['vid'=>$id, 'site_id'=>$this->site_id, 'status'=>'normal']);
        $episodes = $episodes->order('weigh desc, id desc')->select();

        $can = 0;
        $nocan = 0;
        $total_num = 0;
        foreach ($episodes as &$item){
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            $item['updatetime'] = date('Y-m-d H:i:s', $item['updatetime']);
            $item['views'] = $item['views'] + $item['fake_views'];
            $item['likes'] = $item['likes'] + $item['fake_likes'];
            $item['shares'] = $item['shares'] + $item['fake_shares'];
            $item['favorites'] = $item['favorites'] + $item['fake_favorites'];
            $video_auth = $this->checkEpisode($item);
            if($video_auth){
                $item['url'] = $item['video']?cdnurl($item['video'], true):'';
                if($nocan == 0){
                    $can++;
                }
            }else{
                if($nocan == 0){
                    $nocan = $can+1;
                }
                $item['url'] = null;
            }
            $total_num++;
            unset($item['video'],$item['fake_favorites'],$item['fake_shares'],$item['fake_views'],$item['fake_likes']);
            $item['is_like'] = 0;
            $item['is_favorite'] = 0;
            if($user_id){
                $video_like = VideoFavorite::where(['user_id'=>$user_id, 'episode_id'=>$item['id'], 'vid'=>$item['vid'], 'type'=>'like', 'site_id'=>$this->site_id])->find();
                if($video_like){
                    $item['is_like'] = 1;
                }
                $video_favorite = VideoFavorite::where(['user_id'=>$user_id, 'episode_id'=>$item['id'], 'vid'=>$item['vid'], 'type'=>'favorite', 'site_id'=>$this->site_id])->find();
                if($video_favorite){
                    $item['is_favorite'] = 1;
                }
            }
        }
        $info['episodes_list'] = $episodes;

        if($platform == 2){
            $code = $this->request->param('code',false);
            try {
                $wechat = new Wc('wxMiniProgram');
                $json = $wechat->getApp()->auth->session($code);
            }catch (\Exception $e){
                $this->error('小程序配置错误：'.$e->getMessage());
            }

            if (!isset($json['session_key'])) {
                $this->error("获取session_key失败，".$json['errmsg']);
            }
            $key = substr($json['session_key'],0,16);

            $config = json_decode(Config::where(['site_id' => $this->site_id, 'name' => 'wxMiniProgram'])->value('value'), true);

            $wxinfo = [
                'openid' => UserOauth::where(['user_id'=>$this->auth->id,'site_id'=>$this->site_id])->value('openid').'',
                'src_appid' => $config['app_id'].'',
                'drama_id' => $info['xcx_drama_id'].'',
                'serial_list' => '',
                'data_expire_at' => time()+7200
            ];
            if($can == 0){
                $wxinfo['serial_list'] = [
                    //['start_serial_no'=>0,'end_serial_no'=>0,'status'=>1],
                    ['start_serial_no'=>1,'end_serial_no'=>$total_num,'status'=>2]
                ];
            }elseif($nocan == 0){
                $wxinfo['serial_list'] = [
                    ['start_serial_no'=>1,'end_serial_no'=>$can,'status'=>1]
                    //['start_serial_no'=>0,'end_serial_no'=>0,'status'=>2],
                ];
            }else{
                $wxinfo['serial_list'] = [
                    ['start_serial_no'=>1,'end_serial_no'=>$can,'status'=>1],
                    ['start_serial_no'=>$nocan,'end_serial_no'=>$total_num,'status'=>2]
                ];
            }

            //$wxinfo['serial_list'] = json_encode($wxinfo['serial_list']);
            $encryptedData = json_encode($wxinfo);

            /*
            //解密
            $data = 'LHb+riG/WsCP8wqQEIvJxCx59exL+Wjf23R/pTIbC9gZo3yrUrMMoqIb3/zviK0jtt2HBTk4Eke/bmSD/qKwfY277NK6YNv9wML8+loNnjIkPJHj3DHUALEcwxBTOUVqgTBNTFimtdU7qwkqMIMU1RSlQR+U9CFckbX46mCQoyA8VssxP5ej16+WIcnVqGbkZQdI5hoWZqvwRdnQtMV3s5ZDlGxxYS75XoTYMNdNYOS6bA9jt4K1AUWuEQcOw8ygs4a8EqYbwnA7uD6Ks+54O6xtEXmLyr5uPZKXcbyzjWs17+odJHI+e0gU5o79e3I6';
            $key = '89283sdkj1212121212';
            $decrypted = openssl_decrypt(base64_decode($data), 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

            dump($decrypted);
            die;
            */

            $encryptedData = openssl_encrypt($encryptedData, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);

            $info['wxinfo'] = [
                'encryptedData' => base64_encode($encryptedData),
                'free' => $can
            ];
        }

        $this->success('短剧详情', $info);
    }

    /**
     * 推荐视频
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recommend(){
        $pagesize = $this->request->get('pagesize', 10);
        $platform = $this->request->get('platform', 1);
        $list = VideoEpisodes::getFreeList($this->site_id, $pagesize,$platform);
        $user_id = $this->auth->id;
        $token = $this->match;
        foreach ($list as &$item){
            $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            $item['updatetime'] = date('Y-m-d H:i:s', $item['updatetime']);
            $item['views'] = $item['views'] + $item['fake_views'];
            $item['likes'] = $item['likes'] + $item['fake_likes'];
            $item['shares'] = $item['shares'] + $item['fake_shares'];
            $item['favorites'] = $item['favorites'] + $item['fake_favorites'];
            $item['url'] = $item['video']?cdnurl($item['video'], true):'';
            unset($item['video'],$item['fake_favorites'],$item['fake_shares'],$item['fake_views'],$item['fake_likes']);
            $item['is_like'] = 0;
            if($user_id){
                $video_like = VideoFavorite::where(['user_id'=>$user_id, 'episode_id'=>$item['id'], 'vid'=>$item['vid'], 'type'=>'like', 'site_id'=>$this->site_id])->find();
                if($video_like){
                    $item['is_like'] = 1;
                }
            }
            $video = VideoModel::get($item['vid']);
            if($video){
                $video['views'] = $video['views'] + $video['fake_views'];
                $video['likes'] = $video['likes'] + $video['fake_likes'];
                $video['shares'] = $video['shares'] + $video['fake_shares'];
                $video['favorites'] = $video['favorites'] + $video['fake_favorites'];
                unset($video['fake_favorites'],$video['fake_shares'],$video['fake_views'],$video['fake_likes']);
                $video['is_favorite'] = 0;
                if($user_id){
                    $video_favorite = VideoLog::where(['user_id'=>$user_id, 'vid'=>$item['vid'], 'type'=>'favorite', 'site_id'=>$this->site_id])->find();
                    if($video_favorite){
                        $video['is_favorite'] = 1;
                    }
                }
            }
            $item['video'] = $video;
        }
        if($list){
            $this->$token();
        }
        $this->success('推荐列表', $list);
    }

    /**
     * 点赞/收藏
     * @ApiMethod   (POST)
     * @ApiParams   (name="type", type="string", required=false, description="类型:like=点赞,favorite=收藏")
     * @ApiParams   (name="episode_id", type="integer", required=false, description="剧集ID")
     * @ApiParams   (name="episode_ids", type="integer", required=false, description="剧集ID（批量删除英文下逗号,分隔）")
     */
    public function favorite()
    {
        $params = $this->request->post();
        $result = VideoFavorite::edit($params);
        $this->success($result ? '成功' : '取消', $result);
    }

    /**
     * 点赞/收藏列表
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     */
    public function favoriteList()
    {
        $page = $this->request->get('page', 1);
        $pagesize = $this->request->get('pagesize', 10);
        $list = VideoFavorite::getVideosList($page, $pagesize);
        foreach ($list as &$data){
            if(isset($data['createtime'])){
                $data['createtime'] = date('Y-m-d H:i:s', $data['createtime']);
            }
            if(isset($data['video']) && $data['video']){
                $data['video']['views'] = $data['video']['views'] + $data['video']['fake_views'];
                $data['video']['likes'] = $data['video']['likes'] + $data['video']['fake_likes'];
                $data['video']['shares'] = $data['video']['shares'] + $data['video']['fake_shares'];
                $data['video']['favorites'] = $data['video']['favorites'] + $data['video']['fake_favorites'];
                unset($data['video']['content'],$data['video']['fake_favorites'],$data['video']['fake_shares'],$data['video']['fake_views'],$data['video']['fake_likes']);
            }
            if(isset($data['episode']) && $data['episode']){
                $data['episode']['views'] = $data['episode']['views'] + $data['episode']['fake_views'];
                $data['episode']['likes'] = $data['episode']['likes'] + $data['episode']['fake_likes'];
                $data['episode']['shares'] = $data['episode']['shares'] + $data['episode']['fake_shares'];
                $data['episode']['favorites'] = $data['episode']['favorites'] + $data['episode']['fake_favorites'];
                unset($data['episode']['fake_favorites'],$data['episode']['fake_shares'],$data['episode']['fake_views'],$data['episode']['fake_likes']);
            }
        }

        $this->success('列表', $list);
    }

    /**
     * 编辑追剧/观看记录
     * @ApiMethod   (POST)
     * @ApiParams   (name="vid", type="integer", required=false, description="短剧ID")
     * @ApiParams   (name="episode_id", type="integer", required=false, description="剧集ID")
     * @ApiParams   (name="type", type="string", required=false, description="类型:log=记录,favorite=追剧")
     * @ApiParams   (name="view_time", type="integer", required=false, description="观看时间")
     */
    public function log()
    {
        $params = $this->request->post();

        if(isset($params['serialNo'])){
            $dramaId = $this->request->post('dramaId',false);
            $serialNo = $this->request->post('serialNo',false);
            $vid = \addons\drama\model\Video::where('xcx_drama_id',$dramaId)->value('id');

            $platform = $this->request->param('platform', '');
            $episodes = VideoEpisodes::where('vid', $vid)->where(function($query)use($serialNo){
                $query->where(['name'=>$serialNo])->whereOr(['name'=>'第'.$serialNo.'集']);
            })->find();
            if(empty($episodes)){
                $this->error('视频不存在，请刷新后重试！');
            }
            $params['vid'] = $vid;
            $params['episode_id'] = $episodes['id'];
        }

        try {
            VideoLog::edit($params);
        }catch (\Exception $e){
            $this->error('失败');
        }

        $this->success('成功');
    }

    //统计短剧观看数据用
    public function addlog(){
        $vid = $this->request->post('vid',0);
        $episode_id = $this->request->post('episode_id',0);
        $platform = $this->request->param('platform', 'H5');
        $ip = $this->request->ip();
        $site_id = Config::getSiteId();
        $addlog = $this->request->post('addlog',0);

        if(isset($params['serialNo'])){
            $dramaId = $this->request->post('dramaId',false);
            $serialNo = $this->request->post('serialNo',false);
            $vid = \addons\drama\model\Video::where('xcx_drama_id',$dramaId)->value('id');

            $platform = $this->request->param('platform', '');
            $episodes = VideoEpisodes::where('vid', $vid)->where(function($query)use($serialNo){
                $query->where(['name'=>$serialNo])->whereOr(['name'=>'第'.$serialNo.'集']);
            })->find();
            if(empty($episodes)){
                $this->error('视频不存在，请刷新后重试！');
            }
            $params['vid'] = $vid;
            $params['episode_id'] = $episodes['id'];
        }


        if($vid && $episode_id && in_array($platform,['H5','wxOfficialAccount','wxMiniProgram','App','douyinxcx']) && $addlog) {

            Db::startTrans();
            try {
                if ($this->auth->isLogin()) {

                    //剧集观看
                    $find_e = EpisodesView::where('episodes_id', $episode_id)->lock(true)->find();
                    if ($find_e) {
                        $find_l = ViewLog::where(['episodes_id' => $episode_id, 'user_id' => $this->auth->id])->lock(true)->find();
                        if (!$find_l) {
                            $find_e->{$platform . '_user'} = Db::raw($platform . '_user + 1');
                            $find_e->total_user = Db::raw('total_user + 1');
                        }
                        $find_e->{$platform . '_user_view'} = Db::raw($platform . '_user_view + 1');
                        $find_e->total_user_view = Db::raw('total_user_view + 1');
                        //$find_e->{$platform.'_visitor_view'} = Db::raw($platform . '_visitor_view + 1');
                        //$find_e->total_visitor_view = Db::raw('total_visitor_view + 1');
                        $find_e->{$platform . '_total_view'} = Db::raw($platform . '_total_view + 1');
                        $find_e->total_view = Db::raw('total_view + 1');
                        $find_e->save();
                    } else {
                        EpisodesView::create([
                            'site_id' => $site_id,
                            'video_id' => $vid,
                            'episodes_id' => $episode_id,
                            $platform . '_user' => 1,
                            $platform . '_user_view' => 1,
                            //$platform.'_visitor_view' => 1,
                            $platform . '_total_view' => 1,
                            'total_user' => 1,
                            'total_view' => 1
                        ]);
                    }

                    //剧目观看
                    $find_v = VideoView::where('video_id', $vid)->lock(true)->find();
                    if ($find_v) {
                        $find_l = ViewLog::where(['video_id' => $vid, 'user_id' => $this->auth->id])->lock(true)->find();
                        if (!$find_l) {
                            $find_v->{$platform . '_user'} = Db::raw($platform . '_user + 1');
                            $find_v->total_user = Db::raw('total_user + 1');
                        }
                        $find_v->{$platform . '_user_view'} = Db::raw($platform . '_user_view + 1');
                        $find_v->total_user_view = Db::raw('total_user_view + 1');
                        //$find_e->{$platform.'_visitor_view'} = Db::raw($platform . '_visitor_view + 1');
                        //$find_e->total_visitor_view = Db::raw('total_visitor_view + 1');
                        $find_v->{$platform . '_total_view'} = Db::raw($platform . '_total_view + 1');
                        $find_v->total_view = Db::raw('total_view + 1');
                        $find_v->save();
                    } else {
                        VideoView::create([
                            'site_id' => $site_id,
                            'video_id' => $vid,
                            $platform . '_user' => 1,
                            $platform . '_user_view' => 1,
                            //$platform.'_visitor_view' => 1,
                            $platform . '_total_view' => 1,
                            'total_user' => 1,
                            'total_view' => 1
                        ]);
                    }

                    //日志
                    ViewLog::create([
                        'site_id' => $site_id,
                        'video_id' => $vid,
                        'episodes_id' => $episode_id,
                        'user_id' => $this->auth->id,
                        'ip' => $ip,
                        'type' => 2,
                        'platform' => $platform
                    ]);

                } else {

                    //剧集观看
                    $find_e = EpisodesView::where('episodes_id', $episode_id)->lock(true)->find();
                    if ($find_e) {

                        //$find_e->{$platform.'_user_view'} = Db::raw($platform . '_user_view + 1');
                        //$find_e->total_user_view = Db::raw('total_user_view + 1');
                        $find_e->{$platform . '_visitor_view'} = Db::raw($platform . '_visitor_view + 1');
                        $find_e->total_visitor_view = Db::raw('total_visitor_view + 1');
                        $find_e->{$platform . '_total_view'} = Db::raw($platform . '_total_view + 1');
                        $find_e->total_view = Db::raw('total_view + 1');
                        $find_e->save();

                    } else {
                        EpisodesView::create([
                            'site_id' => $site_id,
                            'video_id' => $vid,
                            'episodes_id' => $episode_id,
                            //$platform.'_user' => 1,
                            //$platform.'_user_view' => 1,
                            $platform . '_visitor_view' => 1,
                            $platform . '_total_view' => 1,
                            'total_user' => 1,
                            'total_view' => 1
                        ]);
                    }

                    //剧目观看
                    $find_v = VideoView::where('video_id', $vid)->lock(true)->find();
                    if ($find_v) {
                        $find_l = ViewLog::where(['video_id' => $vid, 'user_id' => $this->auth->id])->lock(true)->find();
                        if (!$find_l) {
                            $find_v->{$platform . '_user'} = Db::raw($platform . '_user + 1');
                            $find_v->total_user = Db::raw('total_user + 1');
                        }
                        $find_v->{$platform . '_user_view'} = Db::raw($platform . '_user_view + 1');
                        $find_v->total_user_view = Db::raw('total_user_view + 1');
                        //$find_e->{$platform.'_visitor_view'} = Db::raw($platform . '_visitor_view + 1');
                        //$find_e->total_visitor_view = Db::raw('total_visitor_view + 1');
                        $find_v->{$platform . '_total_view'} = Db::raw($platform . '_total_view + 1');
                        $find_v->total_view = Db::raw('total_view + 1');
                        $find_v->save();
                    } else {
                        VideoView::create([
                            'site_id' => $site_id,
                            'video_id' => $vid,
                            $platform . '_user' => 1,
                            $platform . '_user_view' => 1,
                            //$platform.'_visitor_view' => 1,
                            $platform . '_total_view' => 1,
                            'total_user' => 1,
                            'total_view' => 1
                        ]);
                    }

                    //日志
                    ViewLog::create([
                        'site_id' => $site_id,
                        'video_id' => $vid,
                        'episodes_id' => $episode_id,
                        'ip' => $ip,
                        'type' => 1,
                        'platform' => $platform
                    ]);

                }
                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
        $this->success('ok');
    }

    /**
     * 删除追剧/观看记录
     * @ApiMethod   (POST)
     * @ApiParams   (name="ids", type="string", required=false, description="短剧ID（英文下逗号,分隔）")
     * @ApiParams   (name="type", type="string", required=false, description="类型:log=记录,favorite=追剧")
     */
    public function delLog()
    {
        $params = $this->request->param();

        $ids = $this->request->post('ids');
        $type = $this->request->post('type');

        if(isset($params['serialNo'])){
            $dramaId = $this->request->post('dramaId',false);
            $serialNo = $this->request->post('serialNo',false);
            $vid = \addons\drama\model\Video::where('xcx_drama_id',$dramaId)->value('id');

            $platform = $this->request->param('platform', '');
            $episodes = VideoEpisodes::where('vid', $vid)->where(function($query)use($serialNo){
                $query->where(['name'=>$serialNo])->whereOr(['name'=>'第'.$serialNo.'集']);
            })->find();
            if(empty($episodes)){
                $this->error('视频不存在，请刷新后重试！');
            }
            $ids = $vid;
            $params['episode_id'] = $episodes['id'];
        }

        VideoLog::del($ids, $type);
        try {
        }catch (\Exception $e){
            $this->error('失败');
        }
        $this->success('成功');
    }

    /**
     * 追剧/观看记录列表
     * @ApiParams   (name="type", type="string", required=false, description="类型:log=记录,favorite=追剧")
     * @ApiParams   (name="page", type="integer", required=false, description="页数")
     * @ApiParams   (name="pagesize", type="integer", required=false, description="每页数量")
     */
    public function logList()
    {
        $type = $this->request->get('type', 'log');
        $page = $this->request->get('page', 1);
        $pagesize = $this->request->get('pagesize', 10);
        $list = VideoLog::getVideosList($type, $page, $pagesize);
        $user_id = $this->auth->id;
        foreach ($list as &$data){
            if(isset($data['createtime'])){
                $data['createtime'] = date('Y-m-d H:i:s', $data['createtime']);
            }
            if(isset($data['video']) && $data['video']){
                $data['video']['views'] = $data['video']['views'] + $data['video']['fake_views'];
                $data['video']['likes'] = $data['video']['likes'] + $data['video']['fake_likes'];
                $data['video']['shares'] = $data['video']['shares'] + $data['video']['fake_shares'];
                $data['video']['favorites'] = $data['video']['favorites'] + $data['video']['fake_favorites'];
                unset($data['video']['content'],$data['video']['fake_favorites'],$data['video']['fake_shares'],$data['video']['fake_views'],$data['video']['fake_likes']);
            }
            if(isset($data['episode']) && $data['episode']){
                $data['episode']['views'] = $data['episode']['views'] + $data['episode']['fake_views'];
                $data['episode']['likes'] = $data['episode']['likes'] + $data['episode']['fake_likes'];
                $data['episode']['shares'] = $data['episode']['shares'] + $data['episode']['fake_shares'];
                $data['episode']['favorites'] = $data['episode']['favorites'] + $data['episode']['fake_favorites'];
                unset($data['episode']['fake_favorites'],$data['episode']['fake_shares'],$data['episode']['fake_views'],$data['episode']['fake_likes']);


                preg_match_all('/\d+/',$data['episode']['name'],$matches);
                $data['episode']['ji'] = $matches[0][0];

            }

            $data['is_favorite'] = 0;
            if($user_id){
                $video_log = VideoLog::where(['user_id'=>$user_id, 'vid'=>$data['vid'], 'type'=>'favorite',
                    'site_id'=>$this->site_id])->find();
                if($video_log){
                    $data['is_favorite'] = 1;
                }
            }
        }
        $this->success('列表', $list);
    }

    /**
     * 视频播放
     * @ApiMethod   (POST)
     * @ApiParams   (name="vid", type="integer", required=false, description="短剧ID")
     * @ApiParams   (name="episode_id", type="integer", required=false, description="剧集ID")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,App=App")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getEpisodesUrl(){
        $vid = $this->request->post('vid');
        $episode_id = $this->request->post('episode_id');
        $platform = $this->request->param('platform', 'H5');
        $episodes = VideoEpisodes::where('id', $episode_id)->where('vid', $vid)->find();
        if(empty($episodes)){
            $this->error('视频不存在，请刷新后重试！');
        }
        $result = $this->checkEpisode($episodes);
        if($result){
            $this->success('成功', ['url'=>cdnurl($episodes['video'], true)]);
        }else{
            // 购买
            if($this->auth->isLogin()){
                try {
                    VideoOrder::addVideoEpisodes($platform, $episodes);
                }catch (Exception $e){
                    $this->success($e->getMessage());
                }
                $this->success('购买成功', ['url'=>cdnurl($episodes['video'], true)]);
            }
            $this->success('未购买');
        }
    }

    /**
     * 微信小程序解锁剧集
     * @ApiMethod   (POST)
     * @ApiParams   (name="drama_id", type="integer", required=false, description="剧目ID")
     * @ApiParams   (name="episode_id", type="integer", required=false, description="剧集ID")
     * @ApiParams   (name="platform", type="string", required=false, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,App=App")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function jiesuo(){

        $dramaId = $this->request->post('dramaId',false);
        $serialNo = $this->request->post('serialNo',false);
        $vid = \addons\drama\model\Video::where('xcx_drama_id',$dramaId)->value('id');

        //$vid = $this->request->post('vid');
        //$episode_id = $this->request->post('episode_id');
        $platform = $this->request->param('platform', 'douyinxcx');
        $episodes = VideoEpisodes::where('vid', $vid)->where(function($query)use($serialNo){
            $query->where(['name'=>$serialNo])->whereOr(['name'=>'第'.$serialNo.'集']);
        })->find();
        if(empty($episodes)){
            $this->error('视频不存在，请刷新后重试！');
        }
        $result = $this->checkEpisode($episodes);
        if($result){
            $this->success('成功', ['url'=>cdnurl($episodes['video'], true)]);
        }else{
            // 购买
            if($this->auth->isLogin()){
                try {
                    VideoOrder::addVideoEpisodes($platform, $episodes);
                }catch (Exception $e){
                    $this->success($e->getMessage());
                }
                $this->success('购买成功', ['url'=>cdnurl($episodes['video'], true)]);
            }
            $this->success('未购买');
        }
    }

    /**
     * 计算最多批量购买
     * @param string $video_id 短剧ID
     * @param string $usable_id 套餐ID
     * @ApiReturnParams   (name="total_price", type="string", description="全集价格")
     * @ApiReturnParams   (name="auto_price", type="string", description="原价格")
     * @ApiReturnParams   (name="youhui_price", type="string", description="优惠价格")
     * @ApiReturnParams   (name="final_price", type="string", description="最终价格")
     * @ApiReturnParams   (name="rate", type="string", description="优惠百分比")
     * @ApiReturnParams   (name="buy_num", type="string", description="购买数量")
     */
    public function piliang(){

        $params = $this->request->param();
        $vd = $this->validate($params,['video_id|短剧ID'=>'require|number','usable_id|套餐ID'=>'require|number']);
        if($vd !== true){
            $this->error($vd);
        }

        $model_vo = new VideoOrder();
        try{
            $data = $model_vo->piliang($this->site_id,$params['video_id'],$params['usable_id']);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }


        $this->success('ok',$data);
    }

    /**
     * 计算指定数量购买
     * @param string $video_id 短剧ID
     * @param string $usable_id 套餐ID
     * @param string $num 购买剧集数量
     * @ApiReturnParams   (name="auto_price", type="string", description="原价格")
     * @ApiReturnParams   (name="youhui_price", type="string", description="优惠价格")
     * @ApiReturnParams   (name="final_price", type="string", description="最终价格")
     * @ApiReturnParams   (name="rate", type="string", description="优惠百分比")
     * @ApiReturnParams   (name="buy_num", type="string", description="购买数量")
     */
    public function jsnum(){

        $params = $this->request->param();
        $vd = $this->validate($params,['video_id|短剧ID'=>'require|number','usable_id|套餐ID'=>'require|number','num|数量'=>'require|number']);
        if($vd !== true){
            $this->error($vd);
        }

        //计算
        $model_vo = new VideoOrder();
        try{
            $data = $model_vo->jsnum($this->site_id,$params['video_id'],$params['usable_id'],$params['num']);
        }catch(Exception $e){
            $this->error($e->getMessage());
        }

        $this->success('ok',$data);
    }

    /**
     * 免费，会员免费，已购买
     * @param $episodes
     * @return bool
     */
    private function checkEpisode($episodes){
        if($episodes['price'] == 0){
            return true;
        }
        $user = $this->auth->getUser();
        if(empty($user)){
            return false;
        }
        if($episodes['vprice'] == 0 && $user->vip_expiretime > time()){
            return true;
        }
        $episode_id = $episodes['id'];
        $video_order = VideoOrder::where('user_id', $user->id)->where('vid', $episodes['vid'])->where('site_id', $this->site_id)
            ->where(function ($query) use ($episode_id){
                $query->whereOr('episode_id', 0)->whereOr('episode_id', $episode_id);
            })->find();
        if($video_order){
            return true;
        }
        return false;
    }

    //开启选集剧目推荐
    public function wxtuijian(){

        try {
            $wechat = new Wc('wxMiniProgram');
            $tmptoken = $wechat->getAccessToken();
        }catch (HttpException $e){
            $this->error('小程序配置错误：'.$e->getMessage());
        }
        if(isset($tmptoken['access_token'])){
            $access_token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
        }
        $url = 'https://api.weixin.qq.com/wxadrama/setplayerdramarecmdswitch?access_token='.$access_token;
        //"entry_type":1,//1-剧结束 2-选集最右侧推荐 3-剧集profile页相关推荐
    	//"switch_status": true// true-打开 false-关闭，没有打开过的都默认为关闭
        $data = '{"entry_type":2,"switch_status":true}';
        $result = Http::sendRequest($url, $data, 'POST');
        //dump($access_token);
        dump($result);

    }

    //绑定公众号微信
    public function bindwx(){

        try {
            $wechat = new Wc('wxMiniProgram');
            $tmptoken = $wechat->getAccessToken();
        }catch (HttpException $e){
            $this->error('小程序配置错误：'.$e->getMessage());
        }
        if(isset($tmptoken['access_token'])){
            $access_token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
        }
        $url = 'https://api.weixin.gg.com/wxadrama/bindaccount??access_token='.$access_token;

        $options = [
            CURLOPT_HTTPHEADER => ['Content-type: application/json']
        ];

        $data = [
            'appid' => '',
            'bind_type' => 1,
            'bind_appid' => ''
        ];
        $data = json_encode($data);

        //1=绑定，2=解除
        $result = Http::sendRequest($url, $data, 'POST',$options);
        dump($result);

        //dump($tmptoken);

    }

    //绑定公众号微信
    public function gettoken(){

        try {
            $wechat = new Wc('wxMiniProgram');
            $tmptoken = $wechat->getAccessToken();
        }catch (HttpException $e){
            $this->error('小程序配置错误：'.$e->getMessage());
        }
        if(isset($tmptoken['access_token'])){
            $access_token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
        }


        $config1 = json_decode(Config::where(['site_id' => $this->site_id, 'name' => 'wxMiniProgram'])->value('value'), true);
        $config2 = json_decode(Config::where(['site_id' => $this->site_id, 'name' => 'wxOfficialAccount'])->value('value'), true);

        $can1 = 0;
        if(isset($config1['app_id']) && $config1['app_id'] && isset($config2['app_id']) && $config2['app_id']){
            $can1 = 1;
        }else{
            $config1['app_id'] = '';
            $config2['app_id'] = '';
        }

        $bind = \think\Cache::get('bind');
        $can2 = 1;
        if($bind){
            $can1 = 0;
            $can2 = 0;
        }

        $this->success('ok',['access_token'=>$access_token,'appid'=>$config1['app_id'],'bind_appid'=>$config2['app_id'],'can1'=>$can1,'can2'=>$can2]);

    }

    /**
     * 绑定后缓存
     */
    public function huancun(){
        \think\Cache::set('bind',1,86400);
        $this->success('ok');
    }

    /**
     * 获取第几集的信息
     */
    public function getprice(){

        $params = $this->request->param();

        if(isset($params['serialNo'])){
            $dramaId = $this->request->post('dramaId',false);
            $serialNo = $this->request->post('serialNo',false);
            $vid = \addons\drama\model\Video::where('xcx_drama_id',$dramaId)->value('id');

            $episodes = VideoEpisodes::where('vid', $vid)->where(function($query)use($serialNo){
                $query->where(['name'=>$serialNo])->whereOr(['name'=>'第'.$serialNo.'集']);
            })->find();
            if(empty($episodes)){
                $this->error('视频不存在，请刷新后重试！');
            }

            $user = $this->auth->getUser();
            if(empty($user)){
                $this->error('用户不存在');
            }
            if($user->vip_expiretime > time()){
                $this->success('ok',['price'=>$episodes['vprice']]);
            }else{
                $this->success('ok',['price'=>$episodes['price']]);
            }


        }

        $this->error('参数错误');

    }

    public function test(){

        die;

        $model = new UsableOrder();
        $order = $model->where('id','554')->find();

        $array = array (
            'signature' => '4cb32da4d8c40fe6987b661484e16290d90c8097',
            'timestamp' => '1710309308',
            'nonce' => '1689374576',
            'ToUserName' => 'gh_db2ed35e408d',
            'FromUserName' => 'onnJ16xN37-0ofu5q-vSDym6k6_c',
            'CreateTime' => 1710309308,
            'MsgType' => 'event',
            'Event' => 'xpay_goods_deliver_notify',
            'OpenId' => 'onnJ16xN37-0ofu5q-vSDym6k6_c',
            'OutTradeNo' => 'AO202401545497344240184100',
            'WeChatPayInfo' =>
                array (
                    'MchOrderNo' => 'VPO240313135455018187350',
                    'TransactionId' => '4200002151202403136868679133',
                    'PaidTime' => 1710309307,
                ),
            'Env' => 0,
            'GoodsInfo' =>
                array (
                    'ProductId' => 'jifen1',
                    'Quantity' => 1,
                    'OrigPrice' => 1,
                    'ActualPrice' => 1,
                    'Attach' => 'AO202401545497344240184100',
                ),
            'RetryTimes' => 0,
            'addon' => 'drama',
            'controller' => 'wechat',
            'action' => 'index',
            'sign' => 'xupf',
            'type' => 'xcx',
        );

        $notify = [
            'order_sn' => $array['OutTradeNo'],
            'transaction_id' => $array['WeChatPayInfo']['TransactionId'],
            'notify_time' => date('Y-m-d H:i:s', $array['CreateTime']),
            'buyer_email' => $array['OpenId'],
            'payment_json' => json_encode($array),
            'pay_fee' => bcdiv($array['GoodsInfo']['ActualPrice'],100,2),
            'pay_type' => 'wechat'              // 支付方式
        ];

        $model->paymentProcess($order,$notify);
    }

}
