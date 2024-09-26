<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Model;

/**
 * 配置模型
 */
class Richtext extends Model
{

    // 表名,不含前缀
    protected $name = 'drama_richtext';
    // 追加属性
    protected $append = [
    ];
    protected $hidden = ['deletetime'];

    public function getContentAttr($value, $data)
    {
        $content = $data['content'];
        $content = str_replace("<img src=\"/uploads", "<img style=\"width: 100%;!important\" src=\"" . cdnurl("/uploads", true), $content);
        $content = str_replace("<video src=\"/uploads", "<video style=\"width: 100%;!important\" src=\"" . cdnurl("/uploads", true), $content);
        return $content;
    }

}
