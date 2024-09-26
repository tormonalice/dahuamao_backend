<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\model\drama;

use think\Model;
use traits\model\SoftDelete;

class DyLog extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_dy_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
    ];
    




}
