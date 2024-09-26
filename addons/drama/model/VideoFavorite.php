<?php

namespace addons\drama\model;

use think\Model;


class VideoFavorite extends Model
{


    // 表名
    protected $name = 'drama_video_favorite';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
    ];


    public static function edit($params)
    {
        extract($params);
        $type = trim($type);
        $user = User::info();
        //批量删除模式
        if (isset($episode_ids) && $episode_ids) {
            $episode_ids = explode(',', $episode_ids);
            foreach ($episode_ids as $g) {
                $favorite = self::get(['episode_id' => $g, 'user_id' => $user->id, 'type' => $type]);
                if($favorite){
                    $favorite->delete();
                    $field = $favorite->type == 'like'?'likes':'favorites';
                    Video::where('id', $favorite->vid)->setDec($field);
                    VideoEpisodes::where('id', $favorite->episode_id)->setDec($field);
                }
            }
            return false;
        }
        //默认反向增删
        $favorite = self::get(['episode_id' => $episode_id, 'user_id' => $user->id, 'type' => $type]);
        if ($favorite) {
            $favorite->delete();
            $field = $favorite->type == 'like'?'likes':'favorites';
            Video::where('id', $favorite->vid)->setDec($field);
            VideoEpisodes::where('id', $favorite->episode_id)->setDec($field);
            return false;
        }else{
            $site_id = Config::getSiteId();
            $video_episodes = VideoEpisodes::get($episode_id);
            $favorite = self::create([
                'site_id' => $site_id,
                'type' => $type,
                'vid' => $video_episodes->vid,
                'user_id' => $user->id,
                'episode_id' => $episode_id
            ]);
            $field = $type == 'like'?'likes':'favorites';
            Video::where('id', $favorite->vid)->setInc($field);
            VideoEpisodes::where('id', $favorite->episode_id)->setInc($field);
            return true;
        }
    }

    public static function getVideosList($page, $pagesize)
    {
        $user = User::info();

        // 短剧物理删除的，直接删掉
        self::whereNotExists(function ($query) {
            $videoTableName = (new Video())->getQuery()->getTable();
            $tableName = (new self())->getQuery()->getTable();
            $query = $query->table($videoTableName)->where($videoTableName . '.id=' . $tableName . '.vid');

            return $query;
        })->where([
            'user_id' => $user->id
        ])->delete();

        $favoriteData = self::with(['video' => function ($query) {
            $query->removeOption('soft_delete');
        }, 'episode' => function ($query) {
            $query->removeOption('soft_delete');
        }])->where([
            'user_id' => $user->id
        ])->order('createtime', 'DESC')->page($page, $pagesize)->select();

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
