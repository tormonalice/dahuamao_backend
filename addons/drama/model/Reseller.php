<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use addons\drama\model\Share as ShareModel;
use think\Db;
use think\Model;
use traits\model\SoftDelete;

class Reseller extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_reseller';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    protected $hidden = ['createtime', 'deletetime', 'updatetime', 'status', 'weigh'];

    // 追加属性
    protected $append = [
    ];

    public function getContentAttr($value, $data){
        $value = $value ? json_decode($value, true) : (isset($data['content']) && $data['content'] ?
            json_decode($data['content'], true) : null);
        return $value;
    }

    public static function share_user_reseller($share, $user){
        $share_user_1 = User::where('id', $share['share_id'])->find();
        if($share_user_1){
            Db::name('drama_reseller_user')->insert([
                'site_id' => $user->site_id,
                'user_id' => $user->id,
                'parent_id' => $share_user_1['id'],
                'reseller_user_id' => $share_user_1['id'],
                'type' => '1',
                'createtime' => time()
            ]);
        }
        $share_parent = ShareModel::where('user_id', $share['share_id'])->find();
        if($share_parent){
            $share_user_2 = User::where('id', $share_parent['share_id'])->find();
            if($share_user_2){
                Db::name('drama_reseller_user')->insert([
                    'site_id' => $user->site_id,
                    'user_id' => $user->id,
                    'parent_id' => $share['share_id'],
                    'reseller_user_id' => $share_user_2['id'],
                    'type' => '2',
                    'createtime' => time()
                ]);
            }
        }
    }

}
