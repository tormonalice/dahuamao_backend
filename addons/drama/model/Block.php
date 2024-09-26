<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Db;
use think\Model;

/**
 * 区块模型
 */
class Block extends Model
{
    protected $name = "drama_block";
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    protected $hidden = ['createtime', 'updatetime', 'status', 'weigh'];
    // 追加属性
    protected $append = [
    ];


    public function getImageAttr($value, $data)
    {
        $value = $value ? $value : '';
        return cdnurl($value, true);
    }

    /**
     * 获取区块列表
     * @param $params
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getBlockList($params)
    {
        $type = empty($params['type']) ? '' : $params['type'];
        $name = empty($params['name']) ? '' : $params['name'];

        $site_id = Config::getSiteId();
        $where['site_id'] = $site_id;
        $where['status'] = 'normal';
        if ($type !== '') {
            $where['type'] = $type;
        }
        if ($name !== '') {
            $where['name'] = $name;
        }
        $order = 'weigh DESC, id ASC';

        $list = self::where($where)
            ->orderRaw($order)
            ->select();

        return $list;
    }

}
