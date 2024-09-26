<?php

namespace addons\drama\model;

use think\Model;


class VideoImages extends Model
{

    // 表名
    protected $name = 'drama_video_images';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;
    protected $hidden = ['site_id'];

    // 追加属性
    protected $append = [

    ];



    public function getImageAttr($value, $data)
    {
        $value = $value ?: ($data['image'] ?? '');
        return $value ? cdnurl($value, true) : '';
    }


    public function video()
    {
        return $this->belongsTo('Video', 'vid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
