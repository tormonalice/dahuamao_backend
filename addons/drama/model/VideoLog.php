<?php

namespace addons\drama\model;

use think\Model;


class VideoLog extends Model
{

    // 表名
    protected $name = 'drama_video_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
    ];

    public static function edit($params)
    {
        extract($params);
        $user = User::info();
        $episode_id = $episode_id ?? 0;
        $view_time = $view_time ?? 0;
        $favorite = self::get(['vid' => $vid, 'user_id' => $user->id, 'type' => $type]);
        if ($favorite) {
            $favorite->episode_id = $episode_id;
            $favorite->view_time = $view_time;
            $favorite->save();
            if($type == 'log'){
                $favorite = self::get(['vid' => $vid, 'user_id' => $user->id, 'type' => 'favorite']);
                if ($favorite) {
                    $favorite->episode_id = $episode_id;
                    $favorite->view_time = $view_time;
                    $favorite->save();
                }
            }
        }else{
            if(!$episode_id && !$view_time){
                $log = self::get(['vid' => $vid, 'user_id' => $user->id, 'type' => 'log']);
                if($log){
                    $episode_id = $log['episode_id'];
                    $view_time = $log['view_time'];
                }else{
                    $episode = VideoEpisodes::where(['vid'=>$vid, 'status'=>'normal'])->order('weigh desc, id asc')->find();
                    if($episode){
                        $episode_id = $episode['id'];
                    }
                }
            }
            $site_id = Config::getSiteId();
            $favorite = self::create([
                'site_id' => $site_id,
                'type' => $type,
                'view_time' => $view_time,
                'vid' => $vid,
                'user_id' => $user->id,
                'episode_id' => $episode_id
            ]);
            $field = $type == 'favorite'?'favorites':'views';
            Video::where('id', $favorite->vid)->setInc($field);
            VideoEpisodes::where('id', $favorite->episode_id)->setInc($field);
        }
    }

    public static function del($ids, $type){
        $user = User::info();
        //批量删除模式
        if (isset($ids) && $ids) {
            $ids = explode(',', $ids);
            foreach ($ids as $g) {
                $log = self::get(['vid' => $g, 'user_id' => $user->id, 'type'=>$type]);
                if($log){
                    $log->delete();
                    if($type == 'favorite'){
                        $field = 'favorites';
                        Video::where('id', $log->vid)->setDec($field);
                        VideoEpisodes::where('id', $log->episode_id)->setDec($field);
                    }
                }
            }
        }
    }

    public static function getVideosList($type, $page, $pagesize)
    {
        $user = User::info();

        // 短剧物理删除的，直接删掉
        self::whereNotExists(function ($query) {
            $videoTableName = (new Video())->getQuery()->getTable();
            $tableName = (new self())->getQuery()->getTable();
            $query = $query->table($videoTableName)->where($videoTableName . '.id=' . $tableName . '.vid');

            return $query;
        })->where([
            'user_id' => $user->id,
            'type' => $type
        ])->delete();
        $favoriteData = self::with(['video' => function ($query) {
            $query->removeOption('soft_delete');
        }, 'episode' => function ($query) {
            $query->removeOption('soft_delete');
        }])->where([
            'user_id' => $user->id,
            'type' => $type
        ])->order('updatetime', 'DESC')->page($page, $pagesize)->select();

        return $favoriteData;
    }


    public function video()
    {
        return $this->belongsTo('Video', 'vid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function episode()
    {
        return $this->belongsTo('VideoEpisodes', 'episode_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
