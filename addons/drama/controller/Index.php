<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\controller;

use addons\drama\library\Service;
use addons\drama\library\Wechat;
use addons\drama\model\Richtext;
use app\admin\model\AuthRule;
use app\common\library\Menu;
use app\common\model\drama\Point;
use app\common\model\drama\SearchLog;
use fast\Random;
use fast\Tree;
use think\Config;
use think\Config as FaConfig;
use think\Db;
use think\exception\HttpResponseException;

/**
 * 首页相关
 * Class Index
 * @package addons\drama\controller
 * php think addon -a drama -c package
 * php think api -a drama -o api.html --force=true
 * sudo -u www php think drama:chat start d
 */
class Index 
{
    protected $noNeedLogin = ['test', 'init', 'about_us', 'index', 'wordFilter', 'richtext',  'version','wxguanggao','point','searchlog'];
    protected $noNeedRight = '*';

    /**
     * @ApiInternal
     */
    public function index()
    {
        $menu = self::getMenu();
        Menu::upgrade('drama', $menu['new']);

        // TODO 更新drama分站点权限
        $ai_rule_id = Db::name('auth_rule')->where('name', 'drama')->value('id');
        $yx_rule_id = Db::name('auth_rule')->where('name', 'drama/yingxiao')->value('id');
        $children_auth_rules = Db::name('auth_rule')->select();
        $ruleTree = new Tree();
        $ruleTree->init($children_auth_rules);
        $ruleIdList1 = $ruleTree->getChildrenIds($ai_rule_id, true);
        $ruleIdList2 = $ruleTree->getChildrenIds($yx_rule_id, true);
        $rules1 = implode(',', $ruleIdList1);
        $rules2 = implode(',', $ruleIdList2);
        $rules = trim('29,30,32,23,24,25,26,27,28,8,2,7,'.$rules1.','.$rules2, ',');
        //Db::name('auth_group')->where('id', 2)->update(['rules'=>$rules]);

        header('Location: /');
        exit();
    }

    /**
     * @ApiInternal
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private static function getMenu()
    {
        $newMenu = [];
        $config_file = ADDON_PATH . "drama" . DS . 'config' . DS . "menu.php";
        if (is_file($config_file)) {
            $newMenu = include $config_file;
        }
        $oldMenu = AuthRule::where('name','like',"drama%")->select();
        $oldMenu = array_column($oldMenu, null, 'name');
        return ['new' => $newMenu, 'old' => $oldMenu];
    }

    /**
     * 初始化
     * @ApiParams   (name="platform", type="string", required=true, description="平台标识")
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function init()
    {
       

        //$this->success('初始化数据', $data);
        $filePath = ROOT_PATH . 'public/fubi/fubi01.txt';
        if (file_exists($filePath)) {
        $file = fopen($filePath, 'r'); // 打开文件
        $fileSize = filesize($filePath); // 获取文件大小
        $content = fread($file, $fileSize); // 读取文件内容
        fclose($file); // 关闭文件
 
        // 返回响应
        return response($content, 200, ['Content-Type' => 'application/json; charset=utf-8'])->contentType('application/json; charset=utf-8');
        } else {
            return '文件不存在';
        }
    }

    /**
     * 站点相关信息
     * @throws \think\exception\DbException
     */
    public function about_us(){
        $configFields = ['drama', 'wxMiniProgram', 'wxOfficialAccount'];    // 定义设置字段
        $configModel = new \addons\drama\model\Config;
        $config = $configModel->where('site_id', $this->site_id)->where('name', 'in', $configFields)->column('value', 'name');

        // 基本信息
        $dramaConfig = [];
        if(isset($config['drama'])){
            $dramaConfig = @json_decode($config['drama'], true);
            $dramaConfig['logo'] = isset($dramaConfig['logo']) && $dramaConfig['logo'] ? cdnurl($dramaConfig['logo'], true) : '';
            $dramaConfig['company'] = isset($dramaConfig['company']) && $dramaConfig['company'] ? cdnurl($dramaConfig['company'], true) : '';
            $copyrights = $dramaConfig['copyright']['list'] ?? [];
            foreach ($copyrights as &$item){
                $item['image'] = isset($item['image']) && $item['image'] ? cdnurl($item['image'], true) : '';
            }
            $dramaConfig['copyright'] = $copyrights;
            $info = get_addon_info('drama');
            $dramaConfig['version'] = $info['version'];
            if(isset($dramaConfig['user_protocol']) && $dramaConfig['user_protocol']){
                $dramaConfig['user_protocol'] = Richtext::get($dramaConfig['user_protocol']);
            }else{
                $dramaConfig['user_protocol'] = null;
            }
            if(isset($dramaConfig['privacy_protocol']) && $dramaConfig['privacy_protocol']){
                $dramaConfig['privacy_protocol'] = Richtext::get($dramaConfig['privacy_protocol']);
            }else{
                $dramaConfig['privacy_protocol'] = null;
            }
            if(isset($dramaConfig['about_us']) && $dramaConfig['about_us']){
                $dramaConfig['about_us'] = Richtext::get($dramaConfig['about_us']);
            }else{
                $dramaConfig['about_us'] = null;
            }
            if(isset($dramaConfig['contact_us']) && $dramaConfig['contact_us']){
                $dramaConfig['contact_us'] = Richtext::get($dramaConfig['contact_us']);
            }else{
                $dramaConfig['contact_us'] = null;
            }
            if(isset($dramaConfig['legal_notice']) && $dramaConfig['legal_notice']){
                $dramaConfig['legal_notice'] = Richtext::get($dramaConfig['legal_notice']);
            }else{
                $dramaConfig['legal_notice'] = null;
            }
            if(isset($dramaConfig['usable_desc']) && $dramaConfig['usable_desc']){
                $dramaConfig['usable_desc'] = Richtext::get($dramaConfig['usable_desc']);
            }else{
                $dramaConfig['usable_desc'] = null;
            }
            if(isset($dramaConfig['vip_desc']) && $dramaConfig['vip_desc']){
                $dramaConfig['vip_desc'] = Richtext::get($dramaConfig['vip_desc']);
            }else{
                $dramaConfig['vip_desc'] = null;
            }
            if(isset($dramaConfig['reseller_desc']) && $dramaConfig['reseller_desc']){
                $dramaConfig['reseller_desc'] = Richtext::get($dramaConfig['reseller_desc']);
            }else{
                $dramaConfig['reseller_desc'] = null;
            }
            unset($dramaConfig['import']);
        }
        $data['system'] = $dramaConfig;

        // 平台信息
        $data['system']['wxOfficialAccount'] = [];
        $data['system']['wxMiniProgram'] = [];
        if(isset($config['wxOfficialAccount']) || isset($config['wxMiniProgram'])){
            $wxOfficialAccountConfig = json_decode($config['wxOfficialAccount'], true);
            $data['system']['wxOfficialAccount']['name'] = $wxOfficialAccountConfig['name'];
            $data['system']['wxOfficialAccount']['qrcode'] = isset($wxOfficialAccountConfig['qrcode']) && $wxOfficialAccountConfig['qrcode'] ? cdnurl($wxOfficialAccountConfig['qrcode'], true) : '';
            $data['system']['wxOfficialAccount']['avatar'] = isset($wxOfficialAccountConfig['avatar']) && $wxOfficialAccountConfig['avatar'] ? cdnurl($wxOfficialAccountConfig['avatar'], true) : '';
            $wxMiniProgramConfig = json_decode($config['wxMiniProgram'], true);
            $data['system']['wxMiniProgram']['name'] = $wxMiniProgramConfig['name'];
            $data['system']['wxMiniProgram']['qrcode'] = isset($wxMiniProgramConfig['qrcode']) && $wxMiniProgramConfig['qrcode'] ? cdnurl($wxMiniProgramConfig['qrcode'], true) : '';
            $data['system']['wxMiniProgram']['avatar'] = isset($wxMiniProgramConfig['avatar']) && $wxMiniProgramConfig['avatar'] ? cdnurl($wxMiniProgramConfig['avatar'], true) : '';
        }
        $this->success('相关信息', $data);
    }

    /**
     * 反馈类型
     */
    public function feedback_type()
    {
        $this->success('反馈类型', array_values(\addons\drama\model\Feedback::$typeAll));
    }

    /**
     * 意见反馈
     * @ApiMethod   (POST)
     * @ApiParams   (name="type", type="string", required=true, description="反馈类型")
     * @ApiParams   (name="phone", type="string", required=true, description="联系电话")
     * @ApiParams   (name="content", type="string", required=true, description="反馈内容")
     * @ApiParams   (name="images", type="string", required=true, description="反馈图片，多个英文下逗号（,）分隔")
     */
    public function feedback() {
        $params = $this->request->post();

        // 表单验证
        $this->dramaValidate($params, get_class(), 'add');

        $this->success('反馈成功', \addons\drama\model\Feedback::add($params));
    }

    /**
     * 敏感词检测
     * @ApiParams   (name="message", type="string", required=true, description="待检测的字符")
     * @ApiParams   (name="is_wx", type="integer", required=false, description="是1否0启用小程序文本内容安全识别，默认否0")
     * @return mixed
     */
    public function wordFilter()
    {
        $message = input('message', '', 'trim');
        $is_wx = input('is_wx', '0', 'trim');
        // 检查繁体字
        if (!Service::isSimpleCn($message)) {
            $this->error('', ['message'=>'检测到字符格式错误']);
        }
        // 自定义敏感词替换
        $is_legal = Service::isContentLegal($message);
        if($is_legal == false){
            $this->error('', ['message'=>'系统检测到敏感信息']);
        }
        // 小程序文本内容安全识别
        if($is_wx){
            $wechat = new Wechat('wxMiniProgram');
            $openid = Db::name('drama_user_oauth')
                ->where('user_id', $this->auth->id)
                ->where('platform', 'wxMiniProgram')
                ->where('provider', 'Wechat')
                ->value('openid');
            if($openid){
                $pass = $wechat->msgSecCheck($openid, $message);
                if (!$pass) {
                    $this->error('', ['message'=>'微信提示内容包含敏感信息']);
                }
            }
        }

        $this->success('敏感词检测', ['message'=>$message]);
    }

    /**
     * 富文本详情
     * @ApiParams   (name="id", type="string", required=true, description="富文本ID")
     * @throws \think\exception\DbException
     */
    public function richtext()
    {
        $id = $this->request->get('id');
        $data = \addons\drama\model\Richtext::get(['id' => $id, 'site_id'=>$this->site_id]);
        $this->success($data->title, $data);
    }

    /**
     * 版本更新信息
     */
    public function version(){
        // $oldversion = $this->request->get('oldversion', '');
        // $data = null;
        // if($oldversion != ''){
        //    $data = Db::name('drama_version')->where('oldversion', $oldversion)->find();
        // }
        $data = Db::name('drama_version')
            ->where('site_id', $this->site_id)
            ->where('status', 'normal')
            ->order('id', 'desc')
            ->find();
        $data['downloadurl'] = cdnurl($data['downloadurl'], true);
        $this->success('ok',$data);
    }

    /**
     * 上传文件
     * @ApiMethod (POST)
     * @ApiParams   (name="file", type="file", required=true, description="文件流")
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = FaConfig::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int)$upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm'])) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证文件后缀
        if (
            $upload['mimetype'] !== '*' &&
            (!in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr))))
        ) {
            $this->error(__('Uploaded file format is limited'));
        }
        //验证是否为图片文件
        $imagewidth = $imageheight = 0;
        if (in_array($fileInfo['type'], ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (!$imgInfo || !isset($imgInfo[0]) || !isset($imgInfo[1])) {
                $this->error(__('Uploaded file is not a valid image'));
            }
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }

        // 文件 md5
        $fileMd5 = md5_file($fileInfo['tmp_name']);

        $replaceArr = [
            '{year}' => date("Y"),
            '{mon}' => date("m"),
            '{day}' => date("d"),
            '{hour}' => date("H"),
            '{min}' => date("i"),
            '{sec}' => date("s"),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => $fileMd5,
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);

        if (in_array($upload['storage'], ['cos', 'alioss', 'qiniu'])) {     // upyun:又拍云 ，bos:百度BOS，ucloud: Ucloud， 如果要使用这三种，请自行安装插件配置，并将标示填入前面数组，进行测试
            $token_name = $upload['storage'] . 'token';     // costoken, aliosstoken, qiniutoken
            $uploads_addon = get_addon_info('uploads');
            if($upload['storage'] == 'alioss' && isset($uploads_addon['state']) && $uploads_addon['state'] == 1){
                $controller_name = '\\addons\\uploads\\controller\\Alioss';
            }elseif($upload['storage'] == 'cos' && isset($uploads_addon['state']) && $uploads_addon['state'] == 1){
                $controller_name = '\\addons\\uploads\\controller\\Cos';
            }elseif(method_exists('\\addons\\' . $upload['storage'] . '\\controller\\Index', 'index')){
                $controller_name = '\\addons\\' . $upload['storage'] . '\\controller\\Index';
            }else{
                $this->error('请先配置云存储插件！');
            }

            $storageToken[$token_name] = $upload['multipart'] && $upload['multipart'][$token_name] ? $upload['multipart'][$token_name] : '';
            $domain = request()->domain();
            try {
                $uploadCreate = \think\Request::create('foo', 'POST', array_merge([
                    'key' => $savekey,
                    'name' => $fileInfo['name'],
                    'md5' => $fileMd5,
                    'chunk' => 0,
                    'site_id' => $this->site_id,
                ], $storageToken));

                // 重新设置跨域允许域名
                $cors = config('fastadmin.cors_request_domain');
                config('fastadmin.cors_request_domain', $cors . ',' . $domain);

                $uploadController = new $controller_name($uploadCreate);
                $uploadController->upload();
            } catch (HttpResponseException $e) {
                $result = $e->getResponse()->getData();
                if (isset($result['code']) && $result['code'] == 0) {
                    $this->error($result['msg']);
                }

                $resultData = $result['data'];
            }
        } else {
            $splInfo = $file->validate(['size' => $size])->move(ROOT_PATH . '/public' . $uploadDir, $fileName);

            if ($splInfo) {
                $resultData = [
                    'url' => $uploadDir . $splInfo->getSaveName(),
                    'fullurl' => request()->domain() . $uploadDir . $splInfo->getSaveName()
                ];
            } else {
                // 上传失败获取错误信息
                $this->error($file->getError());
            }
        }

        $params = array(
            'admin_id' => 0,
            'user_id' => (int)$this->auth->id,
            'site_id' => (int)$this->auth->site_id,
            'filename'    => substr(htmlspecialchars(strip_tags($fileInfo['name'])), 0, 100),
            'filesize' => $fileInfo['size'],
            'imagewidth' => $imagewidth,
            'imageheight' => $imageheight,
            'imagetype' => $suffix,
            'imageframes' => 0,
            'mimetype' => $fileInfo['type'] == 'application/octet-stream' && in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp']) ? 'image/' . $suffix : $fileInfo['type'],
            'url' => $resultData['url'],
            'uploadtime' => time(),
            'storage' => $upload['storage'],
            'sha1' => $sha1,
        );
        $attachment = new \app\common\model\Attachment;
        $attachment->data(array_filter($params));
        $attachment->save();
        \think\Hook::listen("upload_after", $attachment);

        $this->success(__('Upload successful'), $resultData);
    }

    public function wxguanggao(){

        $data = [];
        if($this->auth->isLogin()){
            $data['mgg'] = \app\common\model\User::where('id',$this->auth->id)->value('mgg');
        }else{
            $data['mgg'] = 0;
        }

        $gg = \addons\drama\model\Config::where(['site_id'=>$this->site_id,'name'=>'wxguanggao'])->value('value');
        if($gg){
            $gg = json_decode($gg,true);
            $data['list'] = [
                'gg_dingdan_switch'=> $gg['gg_dingdan_switch']??0,
                'gg_dingdan_id' => $gg['gg_dingdan_id']??'',
                'gg_xiahua_switch'=> $gg['gg_xiahua_switch']??0,
                'gg_xiahua_id'=> $gg['gg_xiahua_id']??'',
                'gg_xiahuatc_switch'=> $gg['gg_xiahuatc_switch']??0,
                'gg_xiahuatc_id'=> $gg['gg_xiahuatc_id']??'',
                'gg_shouye_switch'=> $gg['gg_shouye_switch']??0,
                'gg_shouye_id'=> $gg['gg_shouye_id']??'',
                'gg_xuanji_switch'=> $gg['gg_xuanji_switch']??0,
                'gg_xuanji_id'=> $gg['gg_xuanji_id']??'',
                'gg_liebiao_switch'=> $gg['gg_liebiao_switch']??0,
                'gg_liebiao_id'=> $gg['gg_liebiao_id']??'',
                'gg_zhuiju_switch'=> $gg['gg_zhuiju_switch']??0,
                'gg_zhuiju_id'=> $gg['gg_zhuiju_id']??'',
                'gg_zanting_switch'=> $gg['gg_zanting_switch']??0,
                'gg_zanting_id'=> $gg['gg_zanting_id']??'',
            ];

        }else{

            $data['list'] = [
                'gg_dingdan_switch'=> 0,
                'gg_dingdan_id' => '',
                'gg_xiahua_switch'=> 0,
                'gg_xiahua_id'=> '',
                'gg_shouye_switch'=> 0,
                'gg_shouye_id'=> '',
                'gg_xuanji_switch'=> 0,
                'gg_xuanji_id'=> '',
                'gg_liebiao_switch'=> 0,
                'gg_liebiao_id'=> '',
                'gg_zhuiju_switch'=> 0,
                'gg_zhuiju_id'=> '',
                'gg_zanting_switch'=> 0,
                'gg_zanting_id'=> '',
            ];

        }

        $this->success('ok',$data);


    }


    /**
     * 埋点
     *
     * 传参
     * item_id 剧目id/广告id
     * platform 平台
     * point_type 埋点类型
     * content 搜索内容
     *
     * point_type类型
     * 1=首页点击
     * 2=追剧页点击
     * 3=搜索展示
     * 4=搜索点击
     * 5=底部tab
     * 6=开通会员
     * 7=去充值
     *
     * item_id底部tab
     * 1=首页
     * 2=追剧
     * 3=推荐
     * 4=我的
     * 5=福利
     *
     * item_id广告位置
     * 1=激励
     * 2=订单
     * 3=下滑
     * 4=首页
     * 5=选集
     * 6=列表
     * 7=追剧
     * 8=暂停
     * 9=下滑弹窗
     *
     *
     */
    public function point(){
        
        $filePath = ROOT_PATH . 'public/fubi/fubi04.txt';
        if (file_exists($filePath)) {
        $file = fopen($filePath, 'r'); // 打开文件
        $fileSize = filesize($filePath); // 获取文件大小
        $content = fread($file, $fileSize); // 读取文件内容
        fclose($file); // 关闭文件
 
        // 返回响应
        return response($content, 200, ['Content-Type' => 'application/json; charset=utf-8'])->contentType('application/json; charset=utf-8');
        } else {
            return '文件不存在';
        }
    }

    /**
     * 搜索日志
     *
     * 传参
     * search_content 搜索内容
     * platform 平台
     *
     */
    /*
    public function searchlog(){
        $search_content = $this->request->post('search_content','');
        $platform = $this->request->param('platform', 'H5');
        $ip = $this->request->ip();
        $site_id = \addons\drama\model\Config::getSiteId();


        if(in_array($platform,['H5','wxOfficialAccount','wxMiniProgram','App','douyinxcx']) && $search_content) {

            Db::startTrans();
            try {
                if ($this->auth->isLogin()) {

                    //日志
                    SearchLog::create([
                        'site_id' => $site_id,
                        'search_content' => $search_content,
                        'user_id' => $this->auth->id,
                        'ip' => $ip,
                        'user_type' => 2,
                        'platform' => $platform
                    ]);

                } else {

                    //日志
                    SearchLog::create([
                        'site_id' => $site_id,
                        'search_content' => $search_content,
                        'ip' => $ip,
                        'user_type' => 1,
                        'platform' => $platform
                    ]);

                }
                Db::commit();
            }catch(\Exception $e){
                Db::rollback();
            }
        }

        $this->success('ok');
    }
    */


}
