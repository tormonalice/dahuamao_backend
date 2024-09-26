## 打包要点

* 打包命令
    * php think addon -a drama -c package

* 导入数据
    * 根目录里面的install.sql要包含插件里面的install.sql

* public目录要点
    * 记得PC和H5文件

* info.ini文件要点
    * 版本号version更新
    * 启用状态state设置为0

* .env文件要点
    * 打包后数据库配置信息需要去掉，debug设置为false

* 授权要点
    * base.php文件要加密

* 其他
  * alioss的index.php的附件要增加site_id
  * alioss的配置文件config要去掉配置信息
  * alioss的配置文件info要设置未开启
  * alioss的文件application/extra/addons.php
  
