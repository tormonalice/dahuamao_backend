<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\model\Block as BlockModel;

/**
 * 广告图
 * @ApiInternal
 */
class Block extends Base
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * @ApiTitle    (获取列表)
     * @ApiSummary  (name：UniAPP首页焦点图uniappindexfocus，UniAPP首页广告图uniappindexside，UniAPP个人中心广告图uniappuserside)
     * @ApiMethod   (GET)
     * @ApiParams   (name="type", type="string", required=true, description="类型:focus=焦点图,side=广告图")
     * @ApiParams   (name="name", type="string", required=true, description="标志")
     */
    public function index(){
        $tag['type'] = $this->request->get('type', '');
        $tag['name'] = $this->request->get('name', '');
        $data = BlockModel::getBlockList($tag);
        $this->success('', $data?$data:[]);
    }

}
