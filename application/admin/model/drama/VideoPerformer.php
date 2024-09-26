<?php

namespace app\admin\model\drama;

use think\Model;


class VideoPerformer extends Model
{

    

    

    // 表名
    protected $name = 'drama_video_performer';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'tags_arr',
        'type_text',
    ];

    public function getTypeList(){
        return ['director'=>__('Type director'), 'performer'=>__('Type performer')];
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['type'] ?? '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getTagsArrAttr($value, $data)
    {
        $value = $value ?: ($data['tags'] ?? '');
        $valueArr = $value?explode(',', $value):[];
        return $valueArr;
    }


    public function video()
    {
        return $this->belongsTo('Video', 'vid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
