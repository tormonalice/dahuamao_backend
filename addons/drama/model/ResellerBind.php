<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use addons\drama\exception\Exception;
use think\Model;

class ResellerBind extends Model
{
    // 表名
    protected $name = 'drama_reseller_bind';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';

    // 追加属性
    protected $append = [
    ];

    /**
     * 添加用户经销商
     * @param $reseller_id
     * @return Reseller|array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function add($site_id, $user_id, $reseller_id){
        $user = User::get($user_id);
        if(empty($user)){
            new Exception('用户不存在');
        }
        $reseller = Reseller::where('id', $reseller_id)->where('status', 'normal')->find();
        if(empty($reseller)){
            new Exception('分销商等级不存在');
        }
        $reseller = $reseller->toArray();
        $reseller_bind = self::where('user_id', $user->id)->where('site_id', $site_id)->find();
        if($reseller_bind){
            if($reseller['expire'] > 0){
                // 不是永久
                if($reseller_bind['expiretime'] < time()){
                    // 分销商过期重新设置
                    $reseller_bind->expiretime = strtotime(date('Y-m-d', strtotime('+1 day'))) + $reseller['expire'];
                }else{
                    // 分销商未过期
                    if($reseller_bind['level'] == $reseller['level']){
                        // 同等级时间叠加
                        $reseller_bind->expiretime = $reseller_bind['expiretime'] + $reseller['expire'];
                    }else{
                        // 不同等级覆盖
                        $reseller_bind->expiretime = strtotime(date('Y-m-d', strtotime('+1 day'))) + $reseller['expire'];
                    }
                }
            }else{
                // 永久
                $reseller_bind->expiretime = 0;
            }
            $reseller_bind->level = $reseller['level'];
            $reseller_bind->reseller_json = json_encode($reseller);
            $reseller_bind->save();
        }else{
            $reseller_bind = new self();
            $reseller_bind->site_id = $site_id;
            $reseller_bind->reseller_id = $reseller_id;
            $reseller_bind->user_id = $user->id;
            $reseller_bind->level = $reseller['level'];
            $reseller_bind->reseller_json = json_encode($reseller);
            if($reseller['expire'] > 0){
                $reseller_bind->expiretime = strtotime(date('Y-m-d', strtotime('+1 day'))) + $reseller['expire'];
            }else{
                $reseller_bind->expiretime = 0;
            }
            $reseller_bind->save();
        }
        return $reseller_bind;
    }

    /**
     * 用户分销信息
     * @return array|bool|\PDOStatement|string|Model|null
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function info(){
        $user = User::info();
        if(empty($user)){
            return null;
        }
        $reseller = self::where('user_id', $user->id)
            ->field('user_id,reseller_id,level,reseller_json,expiretime')
            ->find();
        if(empty($reseller)){
            return null;
        }
        if($reseller && ($reseller['expiretime'] > time() || $reseller['expiretime'] == 0)){
            if($reseller['expiretime']){
                $reseller['expiretime_text'] = date('Y-m-d', $reseller['expiretime']);
            }else{
                $reseller['expiretime_text'] = '永久';
            }
            $json = json_decode($reseller['reseller_json'], true);
            $json['image'] = $json['image'] ? cdnurl($json['image'], true) : '';
            $reseller['reseller_json'] = $json;
        }else{
            return null;
        }
        return $reseller;
    }
}
