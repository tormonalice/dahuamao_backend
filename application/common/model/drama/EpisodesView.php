<?php

namespace app\common\model\drama;

use think\Model;


class EpisodesView extends Model
{

    

    

    // 表名
    protected $name = 'drama_episodes_view';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function episodes()
    {
        return $this->belongsTo('app\common\model\drama\video\Episodes', 'episodes_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function video()
    {
        return $this->belongsTo('Video', 'video_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
