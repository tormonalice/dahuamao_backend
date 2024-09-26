<?php
namespace app\job;
use app\common\model\OrderItem;
use app\common\model\ItemSn;
use think\queue\Job;
use think\Db;
use think\Log;

class XcxMeizi
{
    public function fire(Job $job, $data)
    {
        //....这里执行具体的任务
        //$data = json_decode($data,true);

        $time = date('Y-m-d H:i:s');

        if($this->jobDone($data))
        {
            $job->delete();
            print("<info>同步完成，video_id-".$data['video_id'].' --- '.$time."</info>\n");
        }else{

            $job->delete();
            print("<info>同步失败，video_id-".$data['video_id'].' --- '.$time."</info>\n");
            //Log::write("XcxMeizi-同步失败，video_id-".$data['video_id']);


            if ($job->attempts() > 2) {
                //通过这个方法可以检查这个任务已经重试了几次了
                print("<info>任务达到最大重试次数后，失败了，video_id-".$data['video_id']."</info>\n");
                //Log::write("XcxMeizi-任务达到最大重试次数后，失败了，video_id-".$data['video_id']);
                $job->delete();
            }else{
                print("<info>同步失败，将在3秒后再次执行，video_id-".$data['video_id']."</info>\n");
                //Log::write("XcxMeizi-同步失败，将在3秒后再次执行，video_id-".$data['video_id']);
                $job->release(3); //$delay为延迟时间
            }


        }

        //如果任务执行成功后 记得删除任务，不然这个任务会重复执行，直到达到最大重试次数后失败后，执行failed方法
        // $job->delete();
        // 也可以重新发布这个任务
        // $job->release($delay); //$delay为延迟时间
    }
    public function failed($data)
    {
        // ...任务达到最大重试次数后，失败了
    }
    public function jobDone($data)
    {
        print("<info>开始同步，video_id-".$data['video_id']."</info> \n");

        $time = time();
        $time2 = date('Y-m-d H:i:s',$time);

        $video = new \app\admin\model\drama\Video;

        $find = $video->where('id',$data['video_id'])->find();

        if($find){
            print("<info>小程序剧目id-".$find['xcx_drama_id']."</info> \n");
        }

        if(!$find){
            print("<info>数据库未找到该剧目</info>\n");
            return false;
        }elseif(!$find['xcx_drama_id']){
            print("<info>该剧目不存在小程序剧目id</info>\n");
            return false;
        }else{
            try{
                $result = $video->updateMeizi($find);
                if($result['code'] == 0){
                    print("<info>".$result['msg']."</info>\n");
                    return false;
                }else{
                    return true;
                }
            }catch(\Exception $e){
                print("<info>".$e->getMessage()."</info>\n");
                return false;
                //Log::write($e->getMessage());
            }
        }

    }
}