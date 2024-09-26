<?php

namespace app\admin\model\drama;

use app\admin\library\Auth;
use fast\Http;
use fast\Tree;
use think\Cache;
use think\Model;
use traits\model\SoftDelete;
use addons\drama\library\Wechat;
use addons\drama\model\Config;
use EasyWeChat\Kernel\Exceptions\HttpException;
use app\admin\model\Sites;

class Video extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_video';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'flags_arr',
        'tags_arr',
        'category_text',
        'year_text',
        'area_text',
        'flags_text',
        'status_text',
        'tag_list_arr'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getFlagsList()
    {
        return ['hot' => __('Flags hot'), 'recommend' => __('Flags recommend')];
    }

    public function getStatusList()
    {
        return ['up' => __('Status up'), 'down' => __('Status down')];
    }

    public function getCategoryList($site_id)
    {
        $category = Category::where('pid', 0)->where('type', 'video')->where('site_id', $site_id)->find();
        $category_list = [];
        if($category){
            $category_list = Category::where('pid', $category['id'])
                ->where('type', 'video')
                ->orderRaw('weigh desc, id asc')
                ->column('name', 'id');
        }
        return $category_list;
    }

    public function getYearList($site_id)
    {
        $category = Category::where('pid', 0)->where('type', 'year')->where('site_id', $site_id)->find();
        $category_list = [];
        if($category){
            $category_list = Category::where('pid', $category['id'])
                ->where('type', 'year')
                ->orderRaw('weigh desc, id asc')
                ->select();
            if($category_list){
                $category_list = collection($category_list)->toArray();
            }
        }
        return $category_list;
    }

    public function getAreaList($site_id)
    {
        $category = Category::where('pid', 0)->where('type', 'area')->where('site_id', $site_id)->find();
        $category_list = [];
        if($category){
            $category_list = Category::where('pid', $category['id'])
                ->where('type', 'area')
                ->orderRaw('weigh desc, id asc')
                ->select();
            if($category_list){
                $category_list = collection($category_list)->toArray();
            }
        }
        return $category_list;
    }


    public function getCategoryIdsArrAttr($value, $data)
    {
        $arr = $data['category_ids'] ? explode(',', $data['category_ids']) : [];

        $category_ids_arr = [];
        if ($arr) {
            $tree = Tree::instance();
            $site_id = Auth::instance()->id;
            $tree->init(collection(\app\admin\model\drama\Category::where('type', 'video')
                ->where('site_id', $site_id)->order('weigh desc,id desc')->select())->toArray(), 'pid');

            foreach ($arr as $key => $id) {
                $category_ids_arr[] = $tree->getParentsIds($id, true);
            }
        }

        return $category_ids_arr;
    }


    public function getCategoryTextAttr($value, $data)
    {
        $value = $value ?: ($data['category_ids'] ?? '');
        $valueArr = $value?explode(',', $value):[];
        $site_id = Auth::instance()->id;
        $list = $this->getCategoryList($site_id);
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    public function getFlagsArrAttr($value, $data)
    {
        $value = $value ?: ($data['flags'] ?? '');
        $valueArr = $value?explode(',', $value):[];
        return $valueArr;
    }

    public function getTagListArrAttr($value, $data)
    {
        $value = $value ?: ($data['tag_list'] ?? '');
        $valueArr = $value?explode(',', $value):[];
        if(!empty($valueArr)){
            foreach ($valueArr as &$v) {
                $v = (int)$v;
            }
        }
        return $valueArr;
    }

    public function gettagsArrAttr($value, $data)
    {
        $value = $value ?: ($data['tags'] ?? '');
        $valueArr = $value?explode(',', $value):[];
        return $valueArr;
    }


    public function getYearTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['year_id']) ? $data['year_id'] : '');
        $site_id = Auth::instance()->id;
        $yearList = $this->getYearList($site_id);
        $list = [];
        foreach ($yearList as $item){
            $list[$item['id']] = $item['name'];
        }
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAreaTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['area_id']) ? $data['area_id'] : '');
        $site_id = Auth::instance()->id;
        $areaList = $this->getAreaList($site_id);
        $list = [];
        foreach ($areaList as $item){
            $list[$item['id']] = $item['name'];
        }
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getFlagsTextAttr($value, $data)
    {
        $value = $value ?: ($data['flags'] ?? '');
        $valueArr = $value?explode(',', $value):[];
        $list = $this->getFlagsList();
        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setFlagsAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


    public function category()
    {
        return $this->belongsTo('Category', 'category_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //根据分页获取剧目列表
    public function getJumu($site_id = null,$limi = null,$offset = null){

        $config = Config::where('name', 'wxMiniProgram')->where('site_id', $site_id)->value('value');
        $config = json_decode($config, true);

        if(!isset($config['meizi_switch']) || !$config['meizi_switch']){
            //$this->error('请先在系统配置-剧场配置-平台配置-微信小程序打开媒资');
            return ['code'=>'0','msg'=>'请先在系统配置-剧场配置-平台配置-微信小程序打开媒资'];
        }

        //小程序配置检测
        $sign = Sites::where('site_id',$site_id)->value('sign');
        try {
            $wechat = new Wechat('wxMiniProgram',$sign);
            $tmptoken = $wechat->getAccessToken();
        }catch (HttpException $e){
            return ['code'=>'0','msg'=>'小程序配置错误：'.$e->getMessage()];
            exit;
        }
        if(isset($tmptoken['access_token'])){
            $token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
            return ['code'=>'0','msg'=>$tmptoken['errmsg'] ?? '获取token失败'];
            exit;
        }

        $url = "https://api.weixin.qq.com/wxa/sec/vod/listdramas?access_token=".$token;

        $info = Http::sendRequest($url, '{"limit":'.$limi.',"offset":'.$offset.'}', 'post');
        //,[CURLOPT_HEADER => ['Content-Type:application/json']]

        if($info && $info['ret']){
            $info = json_decode($info['msg'],true);
            if($info['errcode'] == 0){
                //dump($info);
                if(!isset($info['drama_info_list']) || empty($info['drama_info_list'])){
                    return ['code'=>'1','data'=> []];
                }else{
                    return ['code'=>'1','data'=> $info['drama_info_list']];
                }
                exit;
            }else{
                return ['code'=>'0','msg'=>'获取剧目失败,'.$info['errmsg']];
                exit;
            }
        }else{
            return ['code'=>'0','msg'=>'小程序接口没有获取到数据'];
            exit;
        }

    }

    //更新剧目里的每集信息
    public function updateMeizi($video_info){

        $time = time();

        $config = Config::where('name', 'wxMiniProgram')->where('site_id', $video_info['site_id'])->value('value');
        $config = json_decode($config, true);

        if(!isset($config['meizi_switch']) || !$config['meizi_switch']){
            $this->error('请先在系统配置-剧场配置-平台配置-微信小程序打开媒资');
        }

        //小程序配置检测
        $sign = Sites::where('site_id',$video_info['site_id'])->value('sign');
        try {
            $wechat = new Wechat('wxMiniProgram',$sign);
            $tmptoken = $wechat->getAccessToken();
        }catch (HttpException $e){
            return ['code'=>'0','msg'=>'小程序配置错误：'.$e->getMessage()];
            exit;
        }
        if(isset($tmptoken['access_token'])){
            $token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
            return ['code'=>'0','msg'=>$tmptoken['errmsg'] ?? '获取token失败'];
            exit;
        }

        //获取剧目信息
        $url = "https://api.weixin.qq.com/wxa/sec/vod/getdrama?access_token=".$token;

        $info = Http::sendRequest($url, '{"drama_id":'.$video_info['xcx_drama_id'].'}', 'post');
        //,[CURLOPT_HEADER => ['Content-Type:application/json']]

        if($info && $info['ret']){
            $info = json_decode($info['msg'],true);
            if($info['errcode'] == 0){
                //dump($info);
                if(!isset($info['drama_info']) || empty($info['drama_info'])){
                    return ['code'=>'0','msg'=>'获取到的信息为空'];
                    exit;
                }elseif($info['drama_info']['audit_detail']['status'] != 3){
                    return ['code'=>'0','msg'=>'该剧未审核通过'];
                    exit;
                }else{

                    if(isset($info['drama_info']['media_list']) && !empty($info['drama_info']['media_list'])){

                        //更新每集视频链接
                        $url = "https://api.weixin.qq.com/wxa/sec/vod/getmedialink?access_token=".$token;

                        foreach($info['drama_info']['media_list'] as $v){
                            $info2 = Http::sendRequest($url, '{"media_id":'.$v['media_id'].',"t":'.($time+7200).'}', 'post');
                            if($info2){
                                $info2 = json_decode($info2['msg'],true);
                                if($info2['errcode'] == 0){
                                    //dump($info2);
                                    if(isset($info2['media_info']) && !empty($info2['media_info'])){
                                        $weigh = str_replace($video_info['title'],'',$info2['media_info']['name']);
                                        $weigh = explode('-',$weigh);
                                        $weigh = end($weigh);

                                        $pattern = '/\d+/';
                                        preg_match_all($pattern,$weigh,$matches);

                                        //$weigh = str_replace('第','',$weigh);
                                        //$weigh = str_replace('集','',$weigh);
                                        //$weigh = explode('.',$weigh);
                                        //$ji = $weigh[0];
                                        $ji = $matches[0][0];

                                        $weigh = $info['drama_info']['media_count'] + 1 - $ji;
                                        $e = \app\admin\model\drama\VideoEpisodes::where(['vid'=>$video_info['id'],'weigh'=>$weigh])->find();

                                        if($e){

                                            $e_info = [
                                                'image'=>$info2['media_info']['cover_url'],
                                                'video'=>$info2['media_info']['mp4_url'],
                                            ];

                                            \app\admin\model\drama\VideoEpisodes::where(['id'=>$e['id']])->update($e_info);
                                        }else{

                                            $e_info = [
                                                'site_id' => $video_info['site_id'],
                                                'vid' => $video_info['id'],
                                                'name' => '第'.$ji.'集',
                                                'image' => $info2['media_info']['cover_url'],
                                                'video' => $info2['media_info']['mp4_url'],
                                                'duration' => $info2['media_info']['duration'],
                                                'price' => 0,
                                                'vprice' => 0,
                                                'sales' => 0,
                                                'likes' => 0,
                                                'views' =>  0,
                                                'favorites' => 0,
                                                'shares' => 0,
                                                'fake_likes' => rand(1000,9000),
                                                'fake_views' => rand(10000,90000),
                                                'fake_favorites' => rand(1000,9000),
                                                'fake_shares' => rand(1000,9000),
                                                'weigh' => $weigh,
                                                'status' => 'normal',
                                                'xcx_media_id' => $info2['media_info']['media_id'],
                                                'createtime' => $time,
                                                'updatetime' => $time,
                                            ];

                                            \app\admin\model\drama\VideoEpisodes::insert($e_info);
                                        }
                                    }
                                }else{
                                    return ['code'=>'0','msg'=>'获取剧集信息失败,'.$info2['errmsg']];
                                    exit;
                                }
                            }
                        }

                        //更新同步状态和封面图和更新时间
                        $update['xcx_sync'] = 1;
                        $update['xcx_update_time'] = $time;
                        $update['image'] = $info['drama_info']['cover_url'];
                        $update['updatetime'] = $time;

                        self::where('id',$video_info['id'])->update($update);

                        return ['code'=>'1'];
                        exit;
                    }else{
                        return ['code'=>'0','msg'=> '该剧不存在媒资视频资源'];
                        exit;
                    }

                }
                exit;
            }else{
                return ['code'=>'0','msg'=>'获取剧目信息失败,'.$info['errmsg']];
                exit;
            }
        }else{
            return ['code'=>'0','msg'=>'小程序接口没有获取到数据 --- '.json_encode($info)];
            exit;
        }

    }

    /**
     *
     * 获取抖音access_token
     */
    public function dy_client_token($site_id){

        $config = Config::where('name', 'douyin')->where('site_id', $site_id)->value('value');
        $config = json_decode($config,true);

        if(!isset($config['meizi_switch']) || !$config['meizi_switch']){
            throw new \Exception('请先在系统配置-剧场配置-平台配置-抖音小程序打开媒资');
        }

        $data['client_key'] = $config['appid'];

        $token = Cache::get('dy_client_token_'.$site_id);


        if(!$token || $token['appid'] != $config['appid']){

            $data['client_secret'] = $config['appsecret'];
            $data['grant_type'] = 'client_credential';

            $headers = ['Content-type: application/json'];
            $options = [
                CURLOPT_HTTPHEADER => $headers
            ];
            $result = Http::sendRequest('https://open.douyin.com/oauth/client_token/', json_encode($data), 'POST', $options);

            if (isset($result['ret']) && $result['ret']) {
                // 请求成功
                $result = json_decode($result['msg'], true);

                if(!isset($result['data']['access_token'])){
                    throw new \Exception('接口验证时 token接口错误,error_code:'.$result['data']['error_code'].','.$result['data']['description']);
                }
            }else{
                // 请求失败
                throw new \Exception('接口验证时 token接口错误,errno:'.$result['errno'].',msg:'.$result['msg']);
            }

            $token['token'] = $result['data']['access_token'];
            $token['appid'] = $config['appid'];

            Cache::set('dy_client_token_'.$site_id,$token,3600);

        }

        return $token;
    }

    //根据分页获取剧目列表
    public function getDyJumu($site_id = null,$limi = null,$page = null){

        try{
            $token = $this->dy_client_token($site_id);
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'获取剧目失败,'.$e->getMessage()];
        }

        $data['page_size'] = $limi;
        $data['page_number'] = $page;
        //审核状态（98审核中，99未审核,不传为审核和未审核均查询）
        //$data['business_status'] = 98;
        //短剧的名称
        //$data['video_name'] = '';

        $headers = ['Content-type: application/json','access-token: '.$token['token']];
        $options = [
            CURLOPT_HTTPHEADER => $headers
        ];
        $result = Http::sendRequest('https://open.douyin.com/oauth/client_token/', json_encode($data), 'POST', $options);

        if (isset($result['ret']) && $result['ret']) {
            // 请求成功
            $result = json_decode($result['msg'], true);

            if($result['err_no']){
                return ['code'=>'0','msg'=>'接口错误,err_no:'.$result['err_no'].','.$result['err_msg']];
            }else{
                return ['code'=>'1','data'=> $result['data']];
            }
        }else{
            // 请求失败
            return ['code'=>'0','msg'=>'接口错误,errno:'.$result['errno'].',msg:'.$result['msg']];
        }


    }

    //更新剧目里的每集信息
    public function updateDyMeizi($video_info){

        $time = time();

        $config = Config::where('name', 'wxMiniProgram')->where('site_id', $video_info['site_id'])->value('value');
        $config = json_decode($config, true);

        if(!isset($config['meizi_switch']) || !$config['meizi_switch']){
            $this->error('请先在系统配置-剧场配置-平台配置-微信小程序打开媒资');
        }

        //小程序配置检测
        $sign = Sites::where('site_id',$video_info['site_id'])->value('sign');
        try {
            $wechat = new Wechat('wxMiniProgram',$sign);
            $tmptoken = $wechat->getAccessToken();
        }catch (HttpException $e){
            return ['code'=>'0','msg'=>'小程序配置错误：'.$e->getMessage()];

        }
        if(isset($tmptoken['access_token'])){
            $token = $tmptoken['access_token'];
        }else{
            $this->error($tmptoken['errmsg'] ?? '获取token失败');
            return ['code'=>'0','msg'=>$tmptoken['errmsg'] ?? '获取token失败'];

        }

        //获取剧目信息
        $url = "https://api.weixin.qq.com/wxa/sec/vod/getdrama?access_token=".$token;

        $info = Http::sendRequest($url, '{"drama_id":'.$video_info['xcx_drama_id'].'}', 'post');
        //,[CURLOPT_HEADER => ['Content-Type:application/json']]

        if($info && $info['ret']){
            $info = json_decode($info['msg'],true);
            if($info['errcode'] == 0){
                //dump($info);
                if(!isset($info['drama_info']) || empty($info['drama_info'])){
                    return ['code'=>'0','msg'=>'获取到的信息为空'];

                }elseif($info['drama_info']['audit_detail']['status'] != 3){
                    return ['code'=>'0','msg'=>'该剧未审核通过'];

                }else{

                    if(isset($info['drama_info']['media_list']) && !empty($info['drama_info']['media_list'])){

                        //更新每集视频链接
                        $url = "https://api.weixin.qq.com/wxa/sec/vod/getmedialink?access_token=".$token;

                        foreach($info['drama_info']['media_list'] as $v){
                            $info2 = Http::sendRequest($url, '{"media_id":'.$v['media_id'].',"t":'.($time+7200).'}', 'post');
                            if($info2){
                                $info2 = json_decode($info2['msg'],true);
                                if($info2['errcode'] == 0){
                                    //dump($info2);
                                    if(isset($info2['media_info']) && !empty($info2['media_info'])){
                                        $weigh = str_replace($video_info['title'],'',$info2['media_info']['name']);
                                        $weigh = explode('-',$weigh);
                                        $weigh = end($weigh);

                                        $pattern = '/\d+/';
                                        preg_match_all($pattern,$weigh,$matches);

                                        //$weigh = str_replace('第','',$weigh);
                                        //$weigh = str_replace('集','',$weigh);
                                        //$weigh = explode('.',$weigh);
                                        //$ji = $weigh[0];
                                        $ji = $matches[0][0];

                                        $weigh = $info['drama_info']['media_count'] + 1 - $ji;
                                        $e = \app\admin\model\drama\VideoEpisodes::where(['vid'=>$video_info['id'],'weigh'=>$weigh])->find();

                                        if($e){

                                            $e_info = [
                                                'image'=>$info2['media_info']['cover_url'],
                                                'video'=>$info2['media_info']['mp4_url'],
                                            ];

                                            \app\admin\model\drama\VideoEpisodes::where(['id'=>$e['id']])->update($e_info);
                                        }else{

                                            $e_info = [
                                                'site_id' => $video_info['site_id'],
                                                'vid' => $video_info['id'],
                                                'name' => '第'.$ji.'集',
                                                'image' => $info2['media_info']['cover_url'],
                                                'video' => $info2['media_info']['mp4_url'],
                                                'duration' => $info2['media_info']['duration'],
                                                'price' => 0,
                                                'vprice' => 0,
                                                'sales' => 0,
                                                'likes' => 0,
                                                'views' =>  0,
                                                'favorites' => 0,
                                                'shares' => 0,
                                                'fake_likes' => rand(1000,9000),
                                                'fake_views' => rand(10000,90000),
                                                'fake_favorites' => rand(1000,9000),
                                                'fake_shares' => rand(1000,9000),
                                                'weigh' => $weigh,
                                                'status' => 'normal',
                                                'xcx_media_id' => $info2['media_info']['media_id'],
                                                'createtime' => $time,
                                                'updatetime' => $time,
                                            ];

                                            \app\admin\model\drama\VideoEpisodes::insert($e_info);
                                        }
                                    }
                                }else{
                                    return ['code'=>'0','msg'=>'获取剧集信息失败,'.$info2['errmsg']];

                                }
                            }
                        }

                        //更新同步状态和封面图和更新时间
                        $update['xcx_sync'] = 1;
                        $update['xcx_update_time'] = $time;
                        $update['image'] = $info['drama_info']['cover_url'];
                        $update['updatetime'] = $time;

                        self::where('id',$video_info['id'])->update($update);

                        return ['code'=>'1'];

                    }else{
                        return ['code'=>'0','msg'=> '该剧不存在媒资视频资源'];

                    }

                }

            }else{
                return ['code'=>'0','msg'=>'获取剧目信息失败,'.$info['errmsg']];

            }
        }else{
            return ['code'=>'0','msg'=>'小程序接口没有获取到数据 --- '.json_encode($info)];

        }

    }

    //回调
    public function setht($site_id){
        try{
            $token = $this->dy_client_token($site_id);
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'操作失败,'.$e->getMessage()];
        }

        //$appid = Cache::get('dy_client_token');
        //$data['ma_app_id'] = $appid['appid'];
        $data['operator'] = '站点管理员';
        $data['release_reason'] = '配置短剧回调地址';
        $data['industry_impl_list'] = [
            'template_id' => 20001,
            'open_ability_impl_list' => [
                'ability_identity' => '/msg/playlet/review/notify',
                'is_delete' => false,
                'test_url' => request()->domain().'/addons/douyin/douyin/notifycallback/site_id/'.$site_id,
                'prod_url' => request()->domain().'/addons/douyin/douyin/notifycallback/site_id/'.$site_id,
                'ability_type' => 2,
                'impl_name' => '短剧回调消息实现配置'
            ],
        ];
        $data['app_config_item_list'] = [];


        $headers = ['Content-type: application/json','access-token: '.$token['token']];
        $options = [
            CURLOPT_HTTPHEADER => $headers
        ];
        $result = Http::sendRequest('https://open.douyin.com/api/industry/v1/solution/set_impl', json_encode($data), 'POST', $options);

        if (isset($result['ret']) && $result['ret']) {
            // 请求成功
            $result = json_decode($result['msg'], true);

            if($result['data']['error_code']){
                return ['code'=>0,'msg'=>'接口错误,error_code:'.$result['data']['error_code'].','.$result['data']['description']];
            }else{
                return ['code'=>1];
            }
        }else{
            // 请求失败
            return ['code'=>0,'msg'=>'接口错误,errno:'.$result['errno'].',msg:'.$result['msg']];
        }
    }

    //抖音同步到内容库
    public function tbnrk($site_id,$id){

        $video = self::where(['id'=>$id,'site_id'=>$site_id])->find();
        if(!$video){
            return ['code'=>'0','msg'=>'短剧不存在'];
        }
        if(!$video['image']){
            return ['code'=>'0','msg'=>'请先添加短剧封面图'];
        }
        if(!$video['open_pic_id']){
            return ['code'=>'0','msg'=>'请先上传短剧封面图到抖音内容库'];
        }
        $year = Category::where('id', $video['year_id'])->where('site_id', $site_id)->value('name');
        if(!$year){
            return ['code'=>'0','msg'=>'请先选择短剧年份'];
        }
        if(mb_strlen($video['recommendation'],"utf-8") > 12){
            return ['code'=>'0','msg'=>'短剧推荐语的最大长度不能大于12'];
        }
        if(mb_strlen($video['description'],"utf-8") > 12){
            return ['code'=>'0','msg'=>'短剧简介的最大长度不能大于200'];
        }
        if(!$video['tag_list']){
            return ['code'=>'0','msg'=>'请先选择短剧类目标签'];
        }else{
            $tag_list = explode(',',$video['tag_list']);
            foreach($tag_list as &$v){
                $v = (int)$v;
            }
        }
        if(!in_array($video['qualification'],[1,2,3,4])){
            return ['code'=>'0','msg'=>'请先选择短剧资质状态'];
        }

        if(!$video['license_num'] && !$video['registration_num'] && !$video['ordinary_record_num'] && !$video['key_record_num']){
            return ['code'=>'0','msg'=>'报审通过的短剧，备案信息至少填写一个'];
        }


        try{
            $token = $this->dy_client_token($site_id);
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'操作失败,'.$e->getMessage()];
        }

        $headers = ['Content-type: application/json','access-token: '.$token['token']];
        $options = [
            CURLOPT_HTTPHEADER => $headers
        ];

        //创建/编辑内容库剧目
        $data = [];
        $data['ma_app_id'] = $token['appid'];
        $data['album_info'] = [
            'title' => $video['title'].'',
            'seq_num' => $video['episodes'],
            'cover_list' => [$video['open_pic_id'].''],
            'year' => $year,
            'album_status' => $video['album_status'],
            'recommendation' => $video['recommendation'].'',
            'desp' => $video['description'].'',
            'tag_list' => $tag_list,
            'qualification' => $video['qualification'],
            'record_info' =>[
                'license_num' => $video['license_num'].'',
                'registration_num' => $video['registration_num'],
                'ordinary_record_num' => $video['ordinary_record_num'],
                'key_record_num' => $video['key_record_num']
            ]
        ];

        if($video['xcx_drama_id']){
            //编辑内容库剧目
            $data['album_id'] = $video['xcx_drama_id'];

            $result = Http::sendRequest('https://open.douyin.com/api/playlet/v2/video/edit/', json_encode($data), 'POST', $options);

            if (isset($result['ret']) && $result['ret']) {
                // 请求成功
                $result = json_decode($result['msg'], true);

                if($result['err_no']){
                    return ['code'=>'0','msg'=>'接口错误,err_no:'.$result['err_no'].','.$result['err_msg']];
                }
            }else{
                // 请求失败
                return ['code'=>'0','msg'=>'接口错误,errno:'.$result['errno'].',msg:'.$result['msg']];
            }

        }else{
            //创建剧目到内容库
            $result = Http::sendRequest('https://open.douyin.com/api/playlet/v2/video/create/', json_encode($data), 'POST', $options);

            if (isset($result['ret']) && $result['ret']) {
                // 请求成功
                $result = json_decode($result['msg'], true);

                if($result['err_no']){
                    return ['code'=>'0','msg'=>'接口错误,err_no:'.$result['err_no'].','.$result['err_msg']];
                }else{
                    //记录内容库剧目id
                    self::where('id',$id)->update(['xcx_drama_id'=>$result['data']['album_id']]);
                }
            }else{
                // 请求失败
                return ['code'=>'0','msg'=>'接口错误,errno:'.$result['errno'].',msg:'.$result['msg']];
            }
        }

        //写日志
        if($video['xcx_drama_id']){
            $operate_content = '内容库编辑短剧';
        }else{
            $operate_content = '内容库创建短剧';
        }

        DyLog::create([
            'operate_content' => $operate_content,
            'site_id' => $site_id,
            'video_id' => $id,
            'album_id' => $result['data']['album_id'],
            'version' => $result['data']['version'],
            //'scope_list' => '',
            //'audit_status' => '',
            //'audit_msg' => ''
        ]);

        return ['code'=>'1'];

    }

    //上传封面图到内容库
    public function uploadfm($site_id,$fmurl){

        try{
            $token = $this->dy_client_token($site_id);
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'操作失败,'.$e->getMessage()];
        }

        $data['resource_type'] = 2;
        $data['ma_app_id'] = $token['appid'];
        $data['image_meta'] = ['url'=>$fmurl];


        $headers = ['Content-type: application/json','access-token: '.$token['token']];
        $options = [
            CURLOPT_HTTPHEADER => $headers
        ];
        $result = Http::sendRequest('https://open.douyin.com/api/playlet/v2/resource/upload/', json_encode($data), 'POST', $options);

        if (isset($result['ret']) && $result['ret']) {
            // 请求成功
            $result = json_decode($result['msg'], true);

            if($result['err_no']){
                return ['code'=>'0','msg'=>'接口错误,err_no:'.$result['err_no'].','.$result['err_msg']];
            }else{
                //记录上传到抖音的图片id
                return ['code'=>1,'data'=>['open_pic_id'=>$result['data']['image_result']['open_pic_id']]];
            }
        }else{
            // 请求失败
            return ['code'=>'0','msg'=>'接口错误,errno:'.$result['errno'].',msg:'.$result['msg']];
        }
    }

    //提审
    public function tishen($site_id,$id){

        $video = self::where(['id'=>$id,'site_id'=>$site_id])->find();
        if(!$video){
            return ['code'=>'0','msg'=>'短剧不存在'];
        }
        if(!$video['xcx_drama_id']){
            return ['code'=>'0','msg'=>'请先同步短剧到内容库'];
        }

        try{
            $token = $this->dy_client_token($site_id);
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'操作失败,'.$e->getMessage()];
        }

        $data['ma_app_id'] = $token['appid'];
        $data['album_id'] = $video['xcx_drama_id'];


        $headers = ['Content-type: application/json','access-token: '.$token['token']];
        $options = [
            CURLOPT_HTTPHEADER => $headers
        ];
        $result = Http::sendRequest('https://open.douyin.com/api/playlet/v2/video/review/', json_encode($data), 'POST', $options);

        if (isset($result['ret']) && $result['ret']) {
            // 请求成功
            $result = json_decode($result['msg'], true);

            if($result['err_no']){
                return ['code'=>'0','msg'=>'接口错误,err_no:'.$result['err_no'].','.$result['err_msg']];
            }else{

                DyLog::create([
                    'operate_content' => '后台提审',
                    'appid' => $token['appid'],
                    'site_id' => $site_id,
                    'video_id' => $id,
                    'album_id' => $video['xcx_drama_id'],
                    'version' => $video['version'],
                    //'scope_list' => '',
                    //'audit_status' => '',
                    //'audit_msg' => ''
                ]);

                //记录上传到抖音的图片id
                return ['code'=>1,'data'=>['version'=>$result['data']['version']]];
            }
        }else{
            // 请求失败
            return ['code'=>'0','msg'=>'接口错误,errno:'.$result['errno'].',msg:'.$result['msg']];
        }
    }

    //同步剧集到内容库
    public function upjuji($site_id,$id){

        $video = self::where(['id'=>$id,'site_id'=>$site_id])->find();
        if(!$video){
            return ['code'=>'0','msg'=>'短剧不存在'];
        }
        if(!$video['xcx_drama_id']){
            return ['code'=>'0','msg'=>'请先同步短剧剧目到内容库'];
        }

        try{
            $token = $this->dy_client_token($site_id);
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'操作失败,'.$e->getMessage()];
        }

        $ve = VideoEpisodes::where('vid',$id)->select();

        if(empty($ve)){
            return ['code'=>'0','msg'=>'请先添加剧集'];
        }

        try {
            foreach ($ve as $v) {

                if (!$v['dy_yun_id']) {
                    continue;
                }

                //上传视频
                $data = [];
                $data['ma_app_id'] = $token['appid'];
                $data['resource_type'] = 1;
                $data['video_meta'] = [
                    'url' => '',
                    'title' => '',
                    'description' => '',
                    'format' => 'mp4',
                    'use_dy_cloud' => false,
                    'dy_cloud_id' => $v['dy_yun_id']
                ];


                $headers = ['Content-type: application/json', 'access-token: ' . $token['token']];
                $options = [
                    CURLOPT_HTTPHEADER => $headers
                ];
                $result = Http::sendRequest('https://open.douyin.com/api/playlet/v2/resource/upload/', json_encode($data), 'POST', $options);

                if (isset($result['ret']) && $result['ret']) {
                    // 请求成功
                    $result = json_decode($result['msg'], true);

                    if ($result['err_no']) {
                        throw new \Exception('接口错误,err_no:' . $result['err_no'] . ',' . $result['err_msg']);
                    } else {

                        VideoEpisodes::where('id',$v['id'])->update(['dy_nrk_id'=>$result['data']['video_result']['open_video_id']]);

                        //剧集同步到内容库剧目下边
                        $ji = str_replace($video['title'],'',$v['name']);
                        $ji = explode('-',$ji);
                        $ji = end($ji);
                        $pattern = '/\d+/';
                        preg_match_all($pattern,$ji,$matches);
                        $ji = $matches[0][0];

                        $data2 = [];
                        $data2 = [
                            'ma_app_id' => $token['appid'],
                            'album_id' => $video['xcx_drama_id'],
                            //'album_info' => [],
                            'episode_info_list' => [
                                'title' => $v['title'],
                                'seq' => (int)$ji,
                                'cover_list' => [$v['open_pic_id'].''],
                                'open_video_id' => $result['data']['video_result']['open_video_id']
                            ]
                        ];
                        $result2 = Http::sendRequest('https://open.douyin.com/api/playlet/v2/video/edit/', json_encode($data2), 'POST', $options);
                        if (isset($result2['ret']) && $result2['ret']) {
                            // 请求成功
                            $result2 = json_decode($result2['msg'], true);

                            if ($result2['err_no']) {
                                throw new \Exception('接口错误,err_no:' . $result2['err_no'] . ',' . $result2['err_msg']);
                            } else {

                            }
                        } else {
                            // 请求失败
                            throw new \Exception('接口错误,errno:' . $result2['errno'] . ',' . $result2['msg']);
                        }


                        DyLog::create([
                            'operate_content' => '同步至内容库',
                            'appid' => $token['appid'],
                            'site_id' => $site_id,
                            'video_id' => $id,
                            've_id' => $v['id'],
                            'album_id' => $video['xcx_drama_id'],
                            'version' => $video['version'],
                            //'scope_list' => '',
                            //'audit_status' => '',
                            //'audit_msg' => '',
                            'extend_json' => json_encode(['log_id'=>$result['log_id'],'dy_nrk_id'=>$result['data']['video_result']['open_video_id'],'dy_yun_id'=>$v['dy_yun_id']])
                        ]);

                    }
                } else {
                    // 请求失败
                    throw new \Exception('接口错误,errno:' . $result['errno'] . ',' . $result['msg']);
                }
            }
        }catch(\Exception $e){
            return ['code'=>'0','msg'=>'请先添加剧集'];
        }

        return ['code'=>'1','msg'=>'操作完成，等待抖音云生成内容库ID'];

    }



}
