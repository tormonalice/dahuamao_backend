<?php

namespace app\admin\model\drama;

use think\Model;
use traits\model\SoftDelete;

class TurntableNum extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_turntable_num';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [

    ];
    

    







    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
