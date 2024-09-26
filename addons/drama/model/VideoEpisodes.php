<?php

namespace addons\drama\model;

use think\Model;
use traits\model\SoftDelete;

class VideoEpisodes extends Model
{

    use SoftDelete;

    

    // 表名
    protected $name = 'drama_video_episodes';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';
    protected $hidden = ['site_id', 'weigh', 'sales', 'status', 'deletetime'];

    // 追加属性
    protected $append = [
    ];


    public function getImageAttr($value, $data)
    {
        $value = $value ?: ($data['image'] ?? '');
        return $value ? cdnurl($value, true) : '';
    }

    /**
     * 推荐列表
     * @param $pagesize
     * @return bool|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getFreeList($site_id, $pagesize,$platform = 1){

        $config = Config::where('name', 'wxMiniProgram')->where('site_id', $site_id)->value('value');
        $config = json_decode($config, true);


        if(!isset($config['meizi_switch']) || !$config['meizi_switch']){
            $platform = 1;
        }

        $model = self::whereExists(function ($query) use($platform){
            $videoTableName = (new Video())->getQuery()->getTable();
            $tableName = (new self())->getQuery()->getTable();
            $query = $query->table($videoTableName)
                ->where($videoTableName . '.id=' . $tableName . '.vid')
                ->whereNull('deletetime')
                ->where('status', 'up')
                ->where('platform',$platform);
            return $query;
        })->where(['status'=>'normal', 'site_id'=>$site_id]);
        $user = User::info();
        if($user && $user->vip_expiretime > time()){
            $model = $model->where(function ($query) {
                $query->where(implode("|", ['price', 'vprice']), 0);
            });
        }else{
            $model = $model->where('price', 0);
        }
        $list = $model->orderRaw('rand()')
            ->limit($pagesize)
            ->select();
        return $list;
    }

    public function video()
    {
        return $this->belongsTo('Video', 'vid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
