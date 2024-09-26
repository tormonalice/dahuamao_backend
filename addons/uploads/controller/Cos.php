<?php

namespace addons\uploads\controller;

use addons\uploads\library\CosAuth;
use app\common\exception\UploadException;
use app\common\library\Upload;
use app\common\model\Attachment;
use Qcloud\Cos\Client;
use Qcloud\Cos\Signature;
use think\addons\Controller;
use think\Config;

/**
 * COS云储存
 *
 */
class Cos extends Controller
{

    protected $cosConfig = [];


    public function _initialize()
    {
        //跨域检测
        check_cors_request();

        parent::_initialize();
        Config::set('default_return_type', 'json');

        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        $this->cosConfig = array(
            'region'      => $config['region'],
            'schema'      => 'https', //协议头部，默认为http
            'credentials' => array(
                'secretId'  => $config['secretId'],
                'secretKey' => $config['secretKey']
            )
        );
    }

    public function index()
    {
        Config::set('default_return_type', 'html');
        $this->error("当前插件暂无前台页面");
    }

    public function params()
    {
        $this->check();

        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        $name = $this->request->post('name');
        $md5 = $this->request->post('md5');
        $chunk = $this->request->post('chunk');

        $key = (new Upload())->getSavekey($config['savekey'], $name, $md5);
        $key = ltrim($key, "/");
        $params = [
            'key' => $key,
            'md5' => $md5
        ];
        if ($chunk) {
            $fileSize = $this->request->post('size');
            $oss = new Client($this->cosConfig);

            $result = $oss->createMultipartUpload(array(
                'Bucket' => $config['bucket'],
                'Key'    => $key,
            ));
            $uploadId = $result['UploadId'];

            $sig = new Signature($config['secretId'], $config['secretKey']);

            $partSize = $this->request->post("chunksize");
            $i = 0;
            $size_count = $fileSize;
            $values = array();
            while ($size_count > 0) {
                $size_count -= $partSize;
                $values[] = array(
                    $partSize * $i,
                    ($size_count > 0) ? $partSize : ($size_count + $partSize),
                );
                $i++;
            }

            $params['key'] = $key;
            $params['uploadId'] = $uploadId;
            $params['partsAuthorization'] = [];
            $date = gmdate('D, d M Y H:i:s \G\M\T');
            foreach ($values as $index => $part) {
                $partNumber = $index + 1;
                $options = array(
                    'Bucket'     => $config['bucket'],
                    'Key'        => $key,
                    'UploadId'   => $uploadId,
                    'PartNumber' => $partNumber,
                    'Body'       => ''
                );
                $command = $oss->getCommand('uploadPart', $options);
                $request = $oss->commandToRequestTransformer($command);
                $authorization = $sig->createAuthorization($request);

                $params['partsAuthorization'][$index] = $authorization;
            }
            $params['date'] = $date;
        } else {
            if ($config['uploadmode'] == 'client') {
                $expiretime = time() + $config['expire'];
                $expiration = gmdate("Y-m-d\TH:i:s.414\Z", $expiretime);
                $keytime = (time() - 60) . ';' . $expiretime;
                $policy = json_encode([
                    'expiration' => $expiration,
                    'conditions' => [
                        ['q-sign-algorithm' => 'sha1'],
                        ['q-ak' => $config['secretId']],
                        ['q-sign-time' => $keytime]
                    ]
                ]);
                $signature = hash_hmac('sha1', sha1($policy), hash_hmac('sha1', $keytime, $config['secretKey']));
                $params = [
                    'key'              => $key,
                    'policy'           => base64_encode($policy),
                    'q-sign-algorithm' => 'sha1',
                    'q-ak'             => $config['secretId'],
                    'q-key-time'       => $keytime,
                    'q-sign-time'      => $keytime,
                    'q-signature'      => $signature
                ];
            }
        }

        $this->success('', null, $params);
        return;
    }

    /**
     * 服务器中转上传文件
     * 上传分片
     * 合并分片
     * @param bool $isApi
     */
    public function upload($isApi = false)
    {
        if ($isApi === true) {
            if (!CosAuth::isModuleAllow()) {
                $this->error("请登录后再进行操作");
            }
        } else {
            $this->check();
        }
        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        $oss = new Client($this->cosConfig);

        //检测删除文件或附件
        $checkDeleteFile = function ($attachment, $upload, $force = false) use ($config) {
            //如果设定为不备份则删除文件和记录 或 强制删除
            if ((isset($config['serverbackup']) && !$config['serverbackup']) || $force) {
                if ($attachment && !empty($attachment['id'])) {
                    $attachment->delete();
                }
                if ($upload) {
                    //文件绝对路径
                    $filePath = $upload->getFile()->getRealPath() ?: $upload->getFile()->getPathname();
                    @unlink($filePath);
                }
            }
        };

        $chunkid = $this->request->post("chunkid");
        if ($chunkid) {
            $action = $this->request->post("action");
            $chunkindex = $this->request->post("chunkindex/d");
            $chunkcount = $this->request->post("chunkcount/d");
            $filesize = $this->request->post("filesize");
            $filename = $this->request->post("filename");
            $method = $this->request->method(true);
            $key = $this->request->post("key");
            $uploadId = $this->request->post("uploadId");

            if ($action == 'merge') {
                $attachment = null;
                $upload = null;
                //合并分片
                if ($config['uploadmode'] == 'server') {
                    //合并分片文件
                    try {
                        $upload = new Upload();
                        $attachment = $upload->merge($chunkid, $chunkcount, $filename);
                    } catch (UploadException $e) {
                        $this->error($e->getMessage());
                    }
                }

                $etags = $this->request->post("etags/a", []);
                if (count($etags) != $chunkcount) {
                    $checkDeleteFile($attachment, $upload, true);
                    $this->error("分片数据错误");
                }
                $listParts = [];
                for ($i = 0; $i < $chunkcount; $i++) {
                    $listParts[] = array("PartNumber" => $i + 1, "ETag" => $etags[$i]);
                }
                try {
                    $result = $oss->completeMultipartUpload(array(
                            'Bucket'   => $config['bucket'],
                            'Key'      => $key,
                            'UploadId' => $uploadId,
                            'Parts'    => $listParts
                        )
                    );
                } catch (\Exception $e) {
                    $checkDeleteFile($attachment, $upload, true);
                    $this->error($e->getMessage());
                }

                if (!isset($result['Key'])) {
                    $checkDeleteFile($attachment, $upload, true);
                    $this->error("上传失败");
                } else {
                    $checkDeleteFile($attachment, $upload);
                    $this->success("上传成功", '', ['url' => "/" . $key, 'fullurl' => cdnurl("/" . $key, true)]);
                }
            } else {
                //默认普通上传文件
                $file = $this->request->file('file');
                try {
                    $upload = new Upload($file);
                    $file = $upload->chunk($chunkid, $chunkindex, $chunkcount);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                try {
                    $params = array(
                        'Bucket'     => $config['bucket'],
                        'Key'        => $key,
                        'UploadId'   => $uploadId,
                        'PartNumber' => $chunkindex + 1,
                        'Body'       => $file->fread($file->getSize())
                    );
                    $ret = $oss->uploadPart($params);
                    $etag = $ret['ETag'];
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                }

                $this->success("上传成功", "", [], 3, ['ETag' => $etag]);
            }
        } else {
            $attachment = null;
            //默认普通上传文件
            $file = $this->request->file('file');
            try {
                $upload = new Upload($file);
                $attachment = $upload->upload();
            } catch (UploadException $e) {
                $this->error($e->getMessage());
            }

            //文件绝对路径
            $filePath = $upload->getFile()->getRealPath() ?: $upload->getFile()->getPathname();

            $url = $attachment->url;

            try {
                $ret = $oss->upload($config['bucket'], ltrim($attachment->url, "/"), $upload->getFile());
                //成功不做任何操作
            } catch (\Exception $e) {
                $checkDeleteFile($attachment, $upload, true);
                $this->error("上传失败：".$e->getMessage());
            }
            $checkDeleteFile($attachment, $upload);

            // 记录云存储记录
            $data = $attachment->toArray();
            unset($data['id']);
            $data['storage'] = 'cos';
            if($this->request->param('site_id')){
                $site_id = $this->request->param('site_id');
            }
            $data['site_id'] = $site_id;

            Attachment::create($data, true);

            $this->success("上传成功", '', ['url' => $url, 'fullurl' => cdnurl($url, true)]);
        }
        return;
    }

    /**
     * 回调
     */
    public function notify()
    {
        $this->check();
        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        if ($config['uploadmode'] != 'client') {
            $this->error("无需执行该操作");
        }
        $this->request->filter('trim,strip_tags,htmlspecialchars,xss_clean');

        $size = $this->request->post('size/d');
        $name = $this->request->post('name', '');
        $md5 = $this->request->post('md5', '');
        $type = $this->request->post('type', '');
        $url = $this->request->post('url', '');
        $width = $this->request->post('width/d');
        $height = $this->request->post('height/d');
        $category = $this->request->post('category', '');
        $category = array_key_exists($category, config('site.attachmentcategory') ?? []) ? $category : '';
        $suffix = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match("/^[a-zA-Z0-9]+$/", $suffix) ? $suffix : 'file';
        $attachment = Attachment::where('site_id', $site_id)->where('url', $url)->where('storage', 'cos')->find();
        if (!$attachment) {
            $params = array(
                'category'    => $category,
                'site_id'     => (int)$site_id,
                'admin_id'    => (int)session('admin.id'),
                'user_id'     => (int)cookie('uid'),
                'filesize'    => $size,
                'filename'    => $name,
                'imagewidth'  => $width,
                'imageheight' => $height,
                'imagetype'   => $suffix,
                'imageframes' => 0,
                'mimetype'    => $type,
                'url'         => $url,
                'uploadtime'  => time(),
                'storage'     => 'cos',
                'sha1'        => $md5,
            );
            Attachment::create($params, true);
        }
        $this->success();
        return;
    }

    /**
     * 检查签名是否正确或过期
     */
    protected function check()
    {
        $costoken = $this->request->post('costoken', '', 'trim');
        if (!$costoken) {
            $this->error("参数不正确(code:1)");
        }
        $site_id = get_site_id();
        $config = get_uploads_config($site_id);
        list($appId, $sign, $data) = explode(':', $costoken);
        if (!$appId || !$sign || !$data) {
            $this->error("参数不正确(code:2)");
        }
        if ($appId !== $config['appId']) {
            $this->error("参数不正确(code:3)");
        }
        if ($sign !== base64_encode(hash_hmac('sha1', base64_decode($data), $config['secretKey'], true))) {
            $this->error("签名不正确");
        }
        $json = json_decode(base64_decode($data), true);
        if ($json['deadline'] < time()) {
            $this->error("请求已经超时");
        }
    }

}
