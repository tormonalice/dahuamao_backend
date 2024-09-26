<?php
/*
  短剧系统
  新版本
*/

namespace app\admin\controller\drama;

use addons\drama\library\SensitiveHelper;
use addons\drama\library\Service;
use app\common\controller\Backend;


/**
 * Ajax异步请求接口
 * @internal
 */
class Ajax extends Backend
{
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);
    }

    /**
     * 检查内容是否包含违禁词
     * @throws \Exception
     */
    public function check_content_islegal()
    {
        $content = $this->request->post('content');
        if (!$content) {
            $this->error(__('请输入检测内容'));
        }
        // 敏感词过滤
        $handle = SensitiveHelper::init()->setTreeByFile(ROOT_PATH . 'addons/drama/library/data/words.dic');
        //首先检测是否合法
        $arr = $handle->getBadWord($content);
        if ($arr) {
            $this->error(__('发现违禁词'), null, $arr);
        } else {
            $this->success(__('未发现违禁词'));
        }
    }

    /**
     * 获取关键字
     * @throws \Exception
     */
    public function get_content_keywords()
    {
        $title = $this->request->post('title');
        $tags = $this->request->post('tags', '');
        $content = $this->request->post('content');
        if (!$content) {
            $this->error(__('Please input your content'));
        }
        $keywords = Service::getContentTags($title);
        $keywords = in_array($title, $keywords) ? [] : $keywords;
        $keywords = array_filter(array_merge([$tags], $keywords));
        $description = mb_substr(strip_tags($content), 0, 200);
        $data = [
            "keywords"    => implode(',', $keywords),
            "description" => $description
        ];
        $this->success("提取成功", null, $data);
    }

}
