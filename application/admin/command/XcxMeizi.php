<?php


namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\Exception;
use think\Queue;
use app\admin\model\drama\Video;
use think\Db;

class XcxMeizi extends Command
{

    protected function configure()
    {
        $this->setName('XcxMeizi')->setDescription('同步小程序媒资视频链接');
    }

    protected function execute(Input $input, Output $output)
    {
        print("<info>开始扫描创建同步小程序媒资视频队列</info> \n");

        //获取更新时间超过50分钟，并且有剧目ID的剧集插入队列中
        $time = time();
        Video::where(['platform'=>2,'xcx_drama_id'=>['gt',0]])->where(function($query)use($time){
            $query->where(['xcx_update_time'=>['lt',$time-3000]])->whereOr('xcx_update_time',null);
        })->chunk(100, function($dramas) {
            foreach ($dramas as $drama) {
                //队列中不存在就添加
                $find = Db('jobs')->where(['queue'=>'XcxMeizi','payload'=>['like','%'.$drama['id'].'%']])->find();
                if(!$find){
                    Queue::push('XcxMeizi', ['video_id'=>$drama['id']], 'XcxMeizi');
                }
            }
        });

        print("<info>扫描插入队列成功</info> \n");

    }

}