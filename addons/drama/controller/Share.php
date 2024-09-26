<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\Config;
use addons\drama\model\Share as ShareModel;
use fast\Random;

/**
 * 分享
 * Class Share
 * @package addons\drama\controller
 */
class Share extends Base
{

    protected $noNeedLogin = [];
    protected $noNeedRight = ['*'];

    
    public function qrcode(){
        $scene = $this->request->get('scene', '');
        if(empty($scene)){
            $scene = $this->auth->id.'.1.0.3.2';
        }
        $path = $this->request->get('path', 'pages/home/index');
        $url = request()->domain().'/addons/drama/wechat/wxacode?scene='.$scene.'&sign='.$this->sign.'&path='.$path;
        $this->success('小程序码', ['qrcode'=>$url]);
    }

    /**
     * 获取分享记录
     * @return void
     */
    public function index()
    {
        $params = $this->request->get();

        $shares = ShareModel::getList($params);
        return $this->success('获取成功', $shares);
    }

    /**
     * 添加上级
     * @ApiParams   (name="id", type="integer", required=true, description="上级用户ID")
     * @ApiParams   (name="platform", type="string", required=true, description="平台:H5=H5,wxOfficialAccount=微信公众号,wxMiniProgram=微信小程序,Web=Web")
     */
    public function add()
    {
        $id = $this->request->get('id');
        $platform = $this->request->get('platform', '');
        $key = array_search($platform, array_keys(ShareModel::getEventMap('share_platform')));
        $spm = $id.'.2.0.'.($key+1).'.4';
        $share = ['spm'=>$spm, 'platform'=>$platform];
        try {
            \think\Db::transaction(function () use ($share) {
                \think\Hook::listen('register_after', $share);
            });
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('绑定成功');
    }

}
