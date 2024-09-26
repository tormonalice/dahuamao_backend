<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Model;
use addons\drama\exception\Exception;
use think\Db;

/**
 * 反馈
 */
class Feedback extends Model
{

    // 表名,不含前缀
    protected $name = 'drama_feedback';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $hidden = ['deletetime'];


    // 追加属性
    protected $append = [
        'type_name', 'status_name'
    ];

    public static $typeAll = [
        'product' => ['code' => 'product', 'name' =>'产品功能问题反馈'],
        'feedback' => ['code' => 'feedback', 'name' => '建议及意见反馈'],
        'complaint' => ['code' => 'complaint', 'name' => '投诉及其他问题'],
    ];

    public static function add($params)
    {
        $user = User::info();
        
        extract($params);
        $site_id = Config::getSiteId();

        $self = self::create([
            'site_id' => $site_id,
            "user_id" => $user->id,
            "type" => $type,
            "content" => $content,
            "images" => implode(',', $images),
            "phone" => $phone,
            'status' => 0
        ]);

        return $self;
    }




    public static function getTypeName($type) {
        return isset(self::$typeAll[$type]) ? self::$typeAll[$type]['name'] : '';
    }


    public function getTypeNameAttr($value, $data) {
        return self::getTypeName($data['type']);
    }

    public function getStatusNameAttr($value, $data) {
        return $data['status'] == 1 ? '已处理' : '未处理';
    }
}
