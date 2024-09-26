<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Model;
use traits\model\SoftDelete;

class Task extends Model
{
    use SoftDelete;

    // 表名
    protected $name = 'drama_task';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'type_text',
    ];

    public static $hooks = [
        'user_register_after' => '新用户首次注册后',
        'user_bind_name_after' => '用户绑定昵称后',
        'user_bind_avatar_after' => '用户绑定头像后',
        'share_wx_after'      => '分享微信后',
        'share_wxf_after'     => '分享微信朋友圈后',
        'share_success'     => '分享成功后(用户注册)',
        'uniad_success'     => '小程序激励视频广告',
    ];

    
    public function getTypeList()
    {
        return ['first' => __('Type first'), 'day' => __('Type day')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }



}
