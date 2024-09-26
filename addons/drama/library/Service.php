<?php
/*
  短剧系统
  新版本
*/

namespace addons\drama\library;

use addons\drama\model\Config;
use addons\drama\library\tpImage\TPImage;
use addons\drama\model\User;
use fast\Random;
use OSS\OssClient;
use think\Config as FaConfig;
use think\Exception;
use GuzzleHttp\Client;


class Service
{
    /**
     * 检测内容是否合法
     * @param string $content 检测内容
     * @param string $type 类型
     * @return bool
     */
    public static function isContentLegal($content, $type = null)
    {
        // 敏感词过滤
        $handle = SensitiveHelper::init()->setTreeByFile(ROOT_PATH . 'addons/drama/library/data/words.dic');
        //首先检测是否合法
        $isLegal = $handle->islegal($content);
        return $isLegal ? true : false;
    }

    /**
     * 获取违禁词
     * @param $content
     * @return string
     * @throws \Exception
     */
    public static function getContentBadWord($content)
    {
        // 敏感词过滤
        $handle = SensitiveHelper::init()->setTreeByFile(ROOT_PATH . 'addons/drama/library/data/words.dic');
        //首先检测是否合法
        $badWord = $handle->getBadWord($content);
        return implode('|', $badWord);
    }

    /**
     * @param $content
     * @return mixed
     * @throws \Exception
     */
    public static function contentReplace($content)
    {
        // 敏感词过滤
        try {
            $handle = SensitiveHelper::init()->setTreeByFile(ROOT_PATH . 'addons/drama/library/data/words.dic');
            $content = $handle->replace($content, '*', '『', '』');
        }catch (Exception $e){
            return $content;
        }
        return $content;
    }

    /**
     * 字符检测
     * @param $message
     * @return bool
     */
    public static function isSimpleCn($message)
    {
        try {
            return iconv('UTF-8', 'GB2312', $message) === false ? false : true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 获取标题的关键字
     * @param $title
     * @return array
     */
    public static function getContentTags($title)
    {
        $arr = [];
        !defined('_VIC_WORD_DICT_PATH_') && define('_VIC_WORD_DICT_PATH_', ROOT_PATH . 'addons/drama/library/data/dict.json');
        $handle = new VicWord('json');
        $result = $handle->getAutoWord($title);
        foreach ($result as $index => $item) {
            $arr[] = $item[0];
        }

        foreach ($arr as $index => $item) {
            if (mb_strlen($item) == 1) {
                unset($arr[$index]);
            }
        }
        return array_filter(array_unique($arr));
    }

    /**
     * 获取AI配置信息
     * @param string $type
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function aiConfig($site_id, $type=null){
        $config = Config::where('site_id', $site_id)->where('name', 'services')->find();        // 读取配置自动缓存 5 分钟
        $config = $config ? json_decode($config['value'], true) : [];
        if($type == null && isset($config['type'])){
            $type = $config['type'];
        }
        $data = $config[$type] ?? [];
        $data['channel'] = $type;
        return $data;
    }

    public static function drawConfig($site_id, $type=null){
        $config = Config::where('site_id', $site_id)->where('name', 'drawAI')->find();        // 读取配置自动缓存 5 分钟
        $config = $config ? json_decode($config['value'], true) : [];
        if($type == null && isset($config['type'])){
            $type = $config['type'];
        }
        $data = $config[$type] ?? [];
        $data['channel'] = $type;
        return $data;
    }

    /**
     * 下载远程图片
     * @param $url
     * @param $savePath
     * @return bool
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function downloadImage($url, $pic_size, $user_id){
        // $time_1 = time();
        $upload = FaConfig::get('upload');
        $resultData = [];
        try {
            $client = new Client();
            $response = $client->request('GET', $url, ['stream' => true, 'timeout' => 0, 'verify' => false, 'allow_redirects' => ['strict' => true]]);
            if ($response->getStatusCode() != 200) { // 判断响应状态码
                return false;
            }
            $contentType = $response->getHeader('Content-Type');
            $contentType = $contentType[0] ?? 'image/png';
            $imageData = $response->getBody()->getContents();
        } catch (\Exception $e) {
            // $e->getMessage();
            return false;
        }
        $image_path = self::getImagePath($url);
        $upload_dir = $image_path['uploadDir'];
        $file_name = $image_path['fileName'];
        $filename = ROOT_PATH.'public'.$upload_dir.$file_name;
        $path = dirname($filename);
        if(!is_dir($path)){
            mkdir($path, 0755, true);
        }
        file_put_contents($filename, $imageData);
        // var_dump('图片保存本地时间：'.(time()-$time_1));
        $imgInfo = getimagesize($filename);
        // var_dump('图片保存oss时间：'.(time()-$time_1));
        $imagewidth = $imageheight = 0;
        $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
        $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        if($upload['storage'] == 'alioss'){
            $config = get_addon_config('alioss');
            $oss = new OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);
            try {
                $oss->uploadFile($config['bucket'], ltrim($upload_dir.$file_name, "/"), $filename);
                //成功不做任何操作
            } catch (\Exception $e) {
                \think\Log::write($e->getMessage());
                return false;
            }
            $imagewidth = intval($imagewidth/2);
            $imageheight = intval($imageheight/2);
            $image_name_list = [
                $file_name,
                $file_name.'?x-oss-process=image/crop,w_'.$imagewidth.',h_'.$imageheight.',g_ne',
                $file_name.'?x-oss-process=image/crop,w_'.$imagewidth.',h_'.$imageheight.',g_se',
                $file_name.'?x-oss-process=image/crop,w_'.$imagewidth.',h_'.$imageheight.',g_nw',
                $file_name.'?x-oss-process=image/crop,w_'.$imagewidth.',h_'.$imageheight.',g_sw'
            ];
            $data = array_slice($image_name_list, 0, $pic_size+1);
        }else{
            $data = self::editImage($image_path, $pic_size);
        }
        foreach ($data as $d){
            self::addAttachment($user_id, $upload_dir, $d, $contentType, $upload['storage']);
            $resultData[] = [
                'url' => $upload_dir . $d,
                'fullurl' => cdnurl($upload_dir . $d, true),
                'imagewidth' => $imagewidth,
                'imageheight' => $imageheight,
            ];
        }
        //如果设定为不备份则删除文件和记录 或 强制删除
        if (isset($config['serverbackup']) && !$config['serverbackup']) {
            if ($upload) {
                //文件绝对路径
                @unlink($filename);
            }
        }else{
            self::addAttachment($user_id, $upload_dir, $file_name, $contentType, 'local');
        }
        // var_dump('图片数据库处理时间：'.(time()-$time_1));
        return $resultData;
    }

    public static function aliossUpload($upload_dir,$file_name,$filenames){
        $config = get_addon_config('alioss');
        $oss = new OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);
        try {
            foreach ($filenames as $filename){
                $oss->uploadFile($config['bucket'], ltrim($upload_dir.$file_name, "/"), $filename);
            }
            //成功不做任何操作
        } catch (\Exception $e) {
            \think\Log::write($e->getMessage());
            return false;
        }
    }

    public static function editImage($image_path, $pic_size){
        $upload_dir = $image_path['uploadDir'];
        $file_name = $image_path['fileName'];
        $file_name_arr = explode('.', $file_name);
        $filename = ROOT_PATH.'public'.$upload_dir.$file_name;
        $file_name1 = $file_name_arr[0].'_1.'.$file_name_arr[1];
        $file_name2 = $file_name_arr[0].'_2.'.$file_name_arr[1];
        $file_name3 = $file_name_arr[0].'_3.'.$file_name_arr[1];
        $file_name4 = $file_name_arr[0].'_4.'.$file_name_arr[1];
        $image = TPImage::open($filename); // 从响应内容中创建图片对象
        //获取图片宽高
        $imageWidth = $image->width();
        $imageHeight = $image->height();
        $image1 = $image->crop($imageWidth / 2, $imageHeight / 2, 0, 0);
        $image1->save(ROOT_PATH.'public'.$upload_dir.$file_name1);
        if($pic_size == 1){
            return [$file_name, $file_name1];
        }
        $image = TPImage::open($filename); // 从响应内容中创建图片对象
        $image2 = $image->crop($imageWidth / 2, $imageHeight / 2, $imageWidth / 2, 0);
        $image2->save(ROOT_PATH.'public'.$upload_dir.$file_name2);
        if($pic_size == 2){
            return [$file_name, $file_name1, $file_name2];
        }
        $image = TPImage::open($filename); // 从响应内容中创建图片对象
        $image3 = $image->crop($imageWidth / 2, $imageHeight / 2, 0, $imageHeight / 2);
        $image3->save(ROOT_PATH.'public'.$upload_dir.$file_name3);
        if($pic_size == 3){
            return [$file_name, $file_name1, $file_name2, $file_name3];
        }
        $image = TPImage::open($filename); // 从响应内容中创建图片对象
        $image4 = $image->crop($imageWidth / 2, $imageHeight / 2, $imageWidth / 2, $imageHeight / 2);
        $image4->save(ROOT_PATH.'public'.$upload_dir.$file_name4);
        return [$file_name, $file_name1, $file_name2, $file_name3, $file_name4];
    }

    public static function getImagePath($url, $ext=''){
        $upload = FaConfig::get('upload');
        $filename = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME);
        $suffix = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $fileMd5 = md5($url);
        if($ext){
            $fileMd5 = md5($url.$ext);
        }
        $replaceArr = [
            '{year}' => date("Y"),
            '{mon}' => date("m"),
            '{day}' => date("d"),
            '{hour}' => date("H"),
            '{min}' => date("i"),
            '{sec}' => date("s"),
            '{random}' => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => substr($filename, 0, strripos($filename, '.')),
            '{suffix}' => $suffix,
            '{.suffix}' => $suffix ? '.' . $suffix : '',
            '{filemd5}' => $fileMd5,
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        return ['uploadDir'=>$uploadDir, 'fileName'=>$fileName];
    }

    public static function addAttachment($user_id, $upload_dir, $file_name, $contentType, $storage){
        if(strpos($file_name, '?') !== false){
            $file_name = explode('?', $file_name)[0];
        }
        $filepath = ROOT_PATH.'public'.$upload_dir.$file_name;
        $imgInfo = getimagesize($filepath);
        $suffix = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        //验证是否为图片文件
        $imagewidth = $imageheight = 0;
        if (in_array($contentType, ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }

        $user = User::get($user_id);
        $params = array(
            'admin_id' => 0,
            'user_id' => (int)$user->id,
            'site_id' => (int)$user->site_id,
            'filename'    => substr(htmlspecialchars(strip_tags($file_name)), 0, 100),
            'filesize' => filesize($filepath),
            'imagewidth' => $imagewidth,
            'imageheight' => $imageheight,
            'imagetype' => $suffix,
            'imageframes' => 0,
            'mimetype' => $contentType,
            'url' => $upload_dir.$file_name,
            'uploadtime' => time(),
            'storage' => $storage,
            'sha1' => hash_file('sha1', $filepath),
        );
        $attachment = new \app\common\model\Attachment;
        $attachment->data(array_filter($params));
        $attachment->save();
        \think\Hook::listen("upload_after", $attachment);
    }
}