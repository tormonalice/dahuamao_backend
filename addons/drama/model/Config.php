<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\model;

use think\Db;
use think\Model;
use addons\drama\exception\Exception;

/**
 * 配置模型
 */
class Config extends Model
{

    // 表名,不含前缀
    protected $name = 'drama_config';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;
    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    // 追加属性
    protected $append = [
    ];

    public static function getEasyWechatConfig($platform, $sign=null)
    {
        $site_id = self::getSiteId($sign);
        $config = json_decode(self::where(['site_id' => $site_id, 'name' => $platform])->value('value'), true);
        return $config;
    }

    public static function getSiteId($sign=null){
        if($sign == null){
            $sign = self::getSiteSign();
        }
        if($sign == ''){
            new Exception('网站地址错误！');
        }
        $site_info = Db::name('sites')->where('sign', $sign)->find();
        return $site_info['site_id'] ?? 0;
    }

    public static function getSiteSign(){
        $sign = request()->header('sign', '');
        if($sign == ''){
            $sign = request()->param('sign', '');
        }
        $sign = substr(trim($sign), 0, 4);
        if($sign == ''){
            $domain = $_SERVER['HTTP_HOST'];
            $site_info = Db::name('sites')->where('domain', $domain)->where('status', 'normal')->find();
            if($site_info){
                $sign = $site_info['sign'] ?? '';
            }else{
                $site_info = Db::name('sites')->where('is_default', 1)->where('status', 'normal')->find();
                $sign = $site_info['sign'] ?? '';
            }
        }
        return $sign;
    }

    /**
     * 本地上传配置信息
     * @return array
     */
    public static function upload()
    {
        $uploadcfg = config('upload');

        $upload = [
            'cdnurl' => $uploadcfg['cdnurl'],
            'uploadurl' => $uploadcfg['uploadurl'],
            'bucket' => 'local',
            'maxsize' => $uploadcfg['maxsize'],
            'mimetype' => $uploadcfg['mimetype'],
            'multipart' => [],
            'multiple' => $uploadcfg['multiple'],
        ];
        return $upload;
    }

}
