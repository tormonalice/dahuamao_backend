<?php

namespace app\admin\model\drama;

use think\Model;

/**
 * 分类模型
 */
class Category extends Model
{

    // 表名,不含前缀
    protected $name = 'drama_category';
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'style_text',
        'type_text',
    ];

    public function getStyleList()
    {
        return ['1' => __('Style 1'), '2' => __('Style 2'), '3' => __('Style 3')];
    }

    public function getTypeList()
    {
        return ['video' => __('Type video'), 'year' => __('Type year'), 'area' => __('Type area')];
    }


    public function getStyleTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['style']) ? $data['style'] : '');
        $list = $this->getStyleList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function children () 
    {
        return $this->hasMany(\app\admin\model\drama\Category::class, 'pid', 'id')->order('weigh desc, id asc');
    }
}
