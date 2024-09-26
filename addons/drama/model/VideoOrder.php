<?php

namespace addons\drama\model;

use think\Db;
use think\Model;
use traits\model\SoftDelete;
use addons\drama\exception\Exception;

class VideoOrder extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'drama_video_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    protected $hidden = ['site_id', 'updatetime', 'deletetime'];

    // 追加属性
    protected $append = [
    ];


    public static function addVideoEpisodes($platform, $episodes){
        $price = $episodes['price'];
        if($price == 0){
            return true;
        }
        $user = User::info();
        $vid = $episodes['vid'];
        $episode_id = $episodes['id'];
        $video_order = self::where('user_id', $user->id)->where('vid', $vid)
            ->where(function ($query) use ($episode_id){
                $query->whereOr('episode_id', 0)->whereOr('episode_id', $episode_id);
            })->find();
        if($video_order){
            return true;
        }
        if($user->vip_expiretime > time()){
            $price = $episodes['vprice'];
            if($price == 0){
                return true;
            }
        }
        // 不能跨集购买
        $episodes_list = VideoEpisodes::where('vid', $vid)
            ->where('weigh', '>=', $episodes['weigh'])
            ->where('id', '<', $episode_id)
            ->where(function ($query) use ($user){
                if($user->vip_expiretime > time()){
                    return $query->where('vprice', '>', 0);
                }else{
                    return $query->where('price', '>', 0);
                }
            })
            ->select();
        foreach ($episodes_list as $item){
            $video_order = self::where('user_id', $user->id)->where('vid', $vid)
                ->where(function ($query) use ($item){
                    $query->whereOr('episode_id', 0)->whereOr('episode_id', $item['id']);
                })->find();
            if(empty($video_order)){
                new Exception('不能跨集购买！');
            }
        }

        $order_sn = self::getSn($user->id);
        Db::transaction(function () use ($user, $vid, $episode_id, $price, $order_sn, $platform){
            $user = User::where('id', $user->id)->lock(true)->find();
            $data = [
                'site_id' => $user->site_id,
                'vid' => $vid,
                'episode_id' => $episode_id,
                'order_sn' => $order_sn,
                'user_id' => $user->id,
                'total_fee' => $price,
                'platform' => $platform,
            ];
            $video_order = self::create($data);
            VideoEpisodes::where('id', $episode_id)->setInc('sales');
            User::usable(-$price, $user, 'used_video', $video_order->id, '追剧支付', [
                'request_id'=>$video_order->id
            ]);
        });
        return true;
    }

    /**
     * 计算批量购买
     */
    public function piliang($site_id,$video_id,$usable_id){

        $user = User::info();
        $time = time();

        $video = Video::where(['id'=>$video_id,'site_id'=>$site_id])->find();
        if(!$video){
            throw new \think\Exception('该剧不存在');
        }

        $usable = Usable::where(['id'=>$usable_id,'site_id'=>$site_id])->find();
        if(!$usable){
            throw new \think\Exception('该套餐不存在');
        }

        $user_usable = $user->usable + $usable->usable;
        //echo $user_usable;

        $data = [];

        //默认集数
        $yh_config = Config::where(['name'=>'batch','site_id'=>$site_id])->value('value');
        if(!$yh_config){
            $yh_config['auto_num'] = 0;
            $yh_config['is_discounts'] = 0;
            $yh_config['discounts'] = [];
            $yh_config['is_piliang'] = 0;
        }else{
            $yh_config = json_decode($yh_config,true);
        }

        //dump($yh_config);

        //剩下的集数和所需要的积分
        $data['total_price'] = 0;
        if($user->vip_expiretime > $time){

            $data['total_num'] = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->order('weigh desc')
                ->count();

            $data['total_price'] = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->order('weigh desc')
                ->sum('ve.vprice');

        }else{

            $data['total_num'] = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->order('weigh desc')
                ->count();

            $data['total_price'] = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->order('weigh desc')
                ->sum('ve.price');

        }


        //剩余剧集数是否够
        $e_count = VideoEpisodes::alias('ve')
            ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
            ->where('ve.vid', $video_id)
            ->where(function ($query) use ($user,$time){
                if($user->vip_expiretime > $time){
                    return $query->where('ve.vprice', '>', 0);
                }else{
                    return $query->where('ve.price', '>', 0);
                }
            })
            ->where('vo.id is null')
            ->order('weigh desc')
            ->count();

        if($e_count < $yh_config['auto_num']){
            $yh_config['auto_num'] = $e_count;
        }
        //dump($yh_config['auto_num']);

        //计算能买的集数最多的价格

        $data['auto_price'] = 0;  //购买价格
        $data['youhui_price'] = 0;  //优惠价格
        $data['final_price'] = 0;  //最终价格
        $data['rate'] = 0;  //优惠百分比
        $data['buy_num'] = 0;  //购买数量
        for($i=$yh_config['auto_num'];$i>0;$i--){

            //echo $i;

            //计算
            $jisuan = $this->jisuan($yh_config,$user,$video_id,$i);
            $data['auto_price'] = $jisuan['auto_price'];
            $data['youhui_price'] = $jisuan['youhui_price'];
            $data['final_price'] = $jisuan['final_price'];
            $data['rate'] = $jisuan['rate'];
            $data['buy_num'] = $jisuan['buy_num'];

            /*
                dump($data['auto_price']);
                dump($data['youhui_price']);
                dump($data['final_price'] );
                dump($user_usable);
                echo "-------------------";
            */

            if($data['final_price'] <= $user_usable){
                $data['buy_num'] = $i;
                break;
            }else{
                //初始化
                $data['auto_price'] = 0;  //购买价格
                $data['youhui_price'] = 0;  //优惠价格
                $data['final_price'] = 0;  //最终价格
                $data['rate'] = 0;  //优惠百分比
                $data['buy_num'] = 0;  //购买数量
            }

        }

        $data['is_piliang'] = $yh_config['is_piliang']??0;

        return $data;

    }

    public function jisuan($yh_config,$user,$video_id,$i){

        $time = time();

        if($i < 1){
            throw new \think\Exception('批量购买至少选择1集');
        }

        $count = VideoEpisodes::alias('ve')
            ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
            ->where('ve.vid', $video_id)
            ->where(function ($query) use ($user,$time){
                if($user->vip_expiretime > $time){
                    return $query->where('ve.vprice', '>', 0);
                }else{
                    return $query->where('ve.price', '>', 0);
                }
            })
            ->where('vo.id is null')
            ->field('ve.vprice')
            ->order('weigh desc')
            ->count();

        if($count < $i){
            throw new \think\Exception('选择数量超出剩余未购买集数');
        }

        //初始化
        $data['auto_price'] = 0;  //购买价格
        $data['youhui_price'] = 0;  //优惠价格
        $data['final_price'] = 0;  //最终价格
        $data['rate'] = 0;  //优惠百分比
        $data['buy_num'] = $i;  //购买数量

        //优惠比例
        if(!isset($yh_config['is_discounts']) || !$yh_config['is_discounts'] || empty($yh_config['discounts'])){
            $data['rate'] = 0;
        }else{
            $a = '初始';
            foreach($yh_config['discounts'] as $v){

                if($i >= $v['full']){

                    if($a == '初始'){
                        $data['rate'] = $v['value'];
                        $a = $v['full'];
                    }elseif($v['full'] > $a){
                        $data['rate'] = $v['value'];
                        $a = $v['full'];
                    }

                }
            }
        }

        //能买的最多的价格
        if($user->vip_expiretime > $time){

            $e_list = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->field('ve.vprice')
                ->order('weigh desc')
                ->limit($i)
                ->select();

            foreach($e_list as $v){
                $data['auto_price'] += $v['vprice'];
            }

            $data['youhui_price'] = bcmul(bcdiv($data['auto_price'],100,2),$data['rate'],0);

        }else{

            $e_list = VideoEpisodes::alias('ve')
                ->join('drama_video_order vo','ve.id = vo.episode_id and vo.user_id = '.$user->id,'left')
                ->where('ve.vid', $video_id)
                ->where(function ($query) use ($user,$time){
                    if($user->vip_expiretime > $time){
                        return $query->where('ve.vprice', '>', 0);
                    }else{
                        return $query->where('ve.price', '>', 0);
                    }
                })
                ->where('vo.id is null')
                ->field('ve.price')
                ->order('weigh desc')
                ->limit($i)
                ->select();

            foreach($e_list as $v){
                $data['auto_price'] += $v['price'];
            }

            $data['youhui_price'] = bcmul(bcdiv($data['auto_price'],100,2),$data['rate'],0);

        }

        $data['final_price'] = $data['auto_price'] - $data['youhui_price'];

        return $data;

    }

    public function jsnum($site_id,$video_id,$usable_id,$num){

        $user = User::info();
        $time = time();

        $video = \addons\drama\model\Video::where(['id'=>$video_id,'site_id'=>$site_id])->find();
        if(!$video){
            throw new \think\Exception('该剧不存在');
        }

        $usable = Usable::where(['id'=>$usable_id,'site_id'=>$site_id])->find();
        if(!$usable){
            throw new \think\Exception('该套餐不存在');
        }

        //能获得的积分
        $user_usable = $user->usable + $usable->usable;

        //配置
        $yh_config = Config::where(['name'=>'batch','site_id'=>$site_id])->value('value');
        if(!$yh_config){
            $yh_config['auto_num'] = 0;
            $yh_config['is_discounts'] = 0;
            $yh_config['discounts'] = [];
        }else{
            $yh_config = json_decode($yh_config,true);
        }

        //计算
        $model_vo = new VideoOrder();
        $data = $model_vo->jisuan($yh_config,$user,$video_id,$num);

        if($data['final_price'] > $user_usable){
            throw new \think\Exception('购买数量超出充值积分');
        }

        return $data;
    }

    public static function addVideos($platform, $video){
        $price = $video['price'];
        if($price == 0){
            return true;
        }
        $user = User::info();
        $id = $video['id'];
        $video_order = self::where('user_id', $user->id)->where('id', $id)
            ->where('episode_id', 0)->find();
        if($video_order){
            return true;
        }
        if($user->vip_expiretime > time()){
            $price = $video['vprice'];
            if($price == 0){
                return true;
            }
        }
        $order_sn = self::getSn($user->id);
        Db::transaction(function () use ($user, $id, $price, $order_sn, $platform){
            $user = User::where('id', $user->id)->lock(true)->find();
            $data = [
                'site_id' => $user->site_id,
                'vid' => $id,
                'episode_id' => 0,
                'order_sn' => $order_sn,
                'user_id' => $user->id,
                'total_fee' => $price,
                'platform' => $platform,
            ];
            $video_order = self::create($data);
            Video::where('id', $id)->setInc('sales');
            User::usable(-$price, $user, 'used_video', $video_order->id, '追剧支付(整集)', [
                'request_id'=>$video_order->id
            ]);
        });
        return true;
    }

    // 获取订单号
    public static function getSn($user_id)
    {
        $rand = $user_id < 9999 ? mt_rand(100000, 99999999) : mt_rand(100, 99999);
        $order_sn = date('Yhis') . $rand;

        $id = str_pad($user_id, (24 - strlen($order_sn)), '0', STR_PAD_BOTH);

        return $order_sn . $id;
    }

    public function video()
    {
        return $this->belongsTo('Video', 'vid', 'id', [], 'LEFT')->setEagerlyType(1);
    }


    public function episode()
    {
        return $this->belongsTo('VideoEpisodes', 'episode_id', 'id', [], 'LEFT')->setEagerlyType(1);
    }
}
