## 修改要点

* addons/drama/controller/Index.php
    * upload()
      if($upload['storage'] == 'alioss' && method_exists('\\addons\\uploads\\controller\\Alioss', 'index')){
      $controller_name = '\\addons\\uploads\\controller\\Alioss';
      }elseif($upload['storage'] == 'cos' && method_exists('\\addons\\uploads\\controller\\Cos', 'index')){
      $controller_name = '\\addons\\uploads\\controller\\Cos';
      }elseif(method_exists('\\addons\\' . $upload['storage'] . '\\controller\\Index', 'index')){
      $controller_name = '\\addons\\' . $upload['storage'] . '\\controller\\Index';
      }else{
      $this->error('请先配置云存储插件！');
      }

* 配置修改
    * platform.html
    * config.js

* application/common/model/Attachment.php
    * if(isset($model['site_id'])){
      $where['site_id'] = $model['site_id'];
      }

* vendor/karsonzhang/fastadmin-addons/src/addons/Service.php
    * download()
      $info = get_addon_info($name);
      if(strpos($info['website'], 'nymaite') !== false){


  
