<?php

namespace addons\uploads;

use addons\uploads\library\AliAuth;
use addons\uploads\library\CosAuth;
use OSS\Core\OssException;
use OSS\OssClient;
use Qcloud\Cos\Client;
use think\Addons;
use think\App;
use think\Loader;

/**
 * 云上传插件
 */
class Uploads extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 添加命名空间
     */
    public function appInit()
    {
        // 公共方法
        require_once __DIR__ . '/helper.php';

        if (!class_exists("\OSS\OssClient")) {
            //添加包的命名空间
            Loader::addNamespace('OSS', ADDON_PATH . 'uploads' . DS . 'library' . DS . 'OSS' . DS);
        }

        if (!class_exists('\Qcloud\Cos\Client')) {
            Loader::addNamespace('Qcloud\Cos', $this->addons_path . str_replace('/', DS, 'library/Qcloud/Cos/'));
        }
        if (!class_exists('\GuzzleHttp\Command\Command')) {
            Loader::addNamespace('GuzzleHttp\Command', $this->addons_path . str_replace('/', DS, 'library/Guzzle/command/src/'));
        }
        if (!class_exists('\GuzzleHttp\Command\Guzzle\Description')) {
            Loader::addNamespace('GuzzleHttp\Command\Guzzle', $this->addons_path . str_replace('/', DS, 'library/Guzzle/guzzle-services/src/'));
        }
        if (!class_exists('\GuzzleHttp\UriTemplate\UriTemplate')) {
            Loader::addNamespace('GuzzleHttp\UriTemplate', $this->addons_path . str_replace('/', DS, 'library/Guzzle/uri-template/src/'));
        }

    }

    /**
     * 判断是否来源于API上传
     */
    public function moduleInit($request)
    {
        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        $module = strtolower($request->module());
        // 判断api/common/upload 是否使用云存储上传
        if ($module == 'api' && ($config['apiupload'] ?? 0) &&
            strtolower($request->controller()) == 'common' &&
            strtolower($request->action()) == 'upload') {
            request()->param('isApi', true);
            if($config['upload_type'] == 'alioss'){
                App::invokeMethod(["\\addons\\uploads\\controller\\Alioss", "upload"], ['isApi' => true]);
            }
            if($config['upload_type'] == 'cos'){
                App::invokeMethod(["\\addons\\uploads\\controller\\Cos", "upload"], ['isApi' => true]);
            }
        }
    }

    /**
     * 加载配置
     */
    public function uploadConfigInit(&$upload)
    {
        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        if(isset($config['upload_type']) && $config['upload_type'] == 'alioss'){
            $data = ['deadline' => time() + $config['expire']];
            $signature = hash_hmac('sha1', json_encode($data), $config['accessKeySecret'], true);

            $token = '';
            if (AliAuth::isModuleAllow()) {
                $token = $config['accessKeyId'] . ':' . base64_encode($signature) . ':' . base64_encode(json_encode($data));
            }
            $multipart = [
                'aliosstoken' => $token
            ];
            $config['uploadurl'] = 'https://' . $config['bucket'] . '.' . $config['endpoint'];
            $upload = array_merge($upload, [
                'cdnurl'     => $config['cdnurl'],
                'uploadurl'  => $config['uploadmode'] == 'client' ? $config['uploadurl'] : addon_url('uploads/alioss/upload', [], false, true),
                'uploadmode' => $config['uploadmode'],
                'bucket'     => $config['bucket'],
                'maxsize'    => $config['maxsize'],
                'mimetype'   => $config['mimetype'],
                'savekey'    => $config['savekey'],
                'chunking'   => (bool)($config['chunking'] ?? $upload['chunking']),
                'chunksize'  => (int)($config['chunksize'] ?? $upload['chunksize']),
                'multipart'  => $multipart,
                'storage'    => $config['upload_type'],
                'multiple'   => (bool)$config['multiple'],
            ]);
        }
        if(isset($config['upload_type']) && $config['upload_type'] == 'cos'){
            $data = ['deadline' => time() + $config['expire']];
            $signature = hash_hmac('sha1', json_encode($data), $config['secretKey'], true);

            $token = '';
            if (CosAuth::isModuleAllow()) {
                $token = $config['appId'] . ':' . base64_encode($signature) . ':' . base64_encode(json_encode($data));
            }
            $multipart = [
                'costoken' => $token
            ];

            $upload = array_merge($upload, [
                'cdnurl'     => $config['cdnurl'],
                'uploadurl'  => $config['uploadmode'] == 'client' ? $config['uploadurl'] : addon_url('uploads/cos/upload', [], false, true),
                'uploadmode' => $config['uploadmode'],
                'bucket'     => $config['bucket'],
                'maxsize'    => $config['maxsize'],
                'mimetype'   => $config['mimetype'],
                'savekey'    => $config['savekey'],
                'chunking'   => (bool)($config['chunking'] ?? $upload['chunking']),
                'chunksize'  => (int)($config['chunksize'] ?? $upload['chunksize']),
                'multipart'  => $multipart,
                'storage'    => $config['upload_type'],
                'multiple'   => (bool)$config['multiple'],
            ]);
        }
    }

    /**
     * 附件删除后
     */
    public function uploadDelete($attachment)
    {
        $site_id = get_site_id();
        $config = get_uploads_config($site_id, $attachment['storage']);
        if ($attachment['storage'] == 'alioss' && isset($config['syncdelete']) && $config['syncdelete']) {
            // 删除云存储端文件
            try {
                $ossClient = new OssClient($config['accessKeyId'], $config['accessKeySecret'], $config['endpoint']);
                $ossClient->deleteObject($config['bucket'], ltrim($attachment->url, '/'));
            } catch (OssException $e) {
                return false;
            }

            //如果是服务端中转，还需要删除本地文件
            //if ($config['uploadmode'] == 'server') {
            //    $filePath = ROOT_PATH . 'public' . str_replace('/', DS, $attachment->url);
            //    if ($filePath) {
            //        @unlink($filePath);
            //    }
            //}
        }
        if ($attachment['storage'] == 'cos' && isset($config['syncdelete']) && $config['syncdelete']) {
            $cosConfig = array(
                'region'      => $config['region'],
                'schema'      => 'https', //协议头部，默认为http
                'credentials' => array(
                    'secretId'  => $config['secretId'],
                    'secretKey' => $config['secretKey']
                )
            );
            $oss = new Client($cosConfig);
            $ret = $oss->deleteObject(array('Bucket' => $config['bucket'], 'Key' => ltrim($attachment->url, '/')));

            //如果是服务端中转，还需要删除本地文件
            //if ($config['uploadmode'] == 'server') {
            //    $filePath = ROOT_PATH . 'public' . str_replace('/', DS, $attachment->url);
            //    if ($filePath) {
            //        @unlink($filePath);
            //    }
            //}
        }
        return true;
    }

}
