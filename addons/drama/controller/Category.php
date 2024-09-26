<?php

namespace addons\drama\controller;

use addons\drama\model\Category as CategoryModel;

/**
 * 分类管理
 * @icon   fa fa-list
 * @remark 用于统一管理网站的所有分类
 */

class Category 
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    /**
     * 分类列表
     */
    public function index()
    {
       
        
        $filePath = ROOT_PATH . 'public/fubi/fubi02.txt';
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


}
