#安装:  
  
1.php 7.4, Mysql 5.7  
  
2.宝塔设置伪静态  

```

location ~* (runtime|application)/{
        return 403;
}
location / {
        if (!-e $request_filename){
                rewrite  ^(.*)$  /index.php?s=$1  last;   break;
        }
}
location ~ .*\.(gif|jpg|jpeg|png|bmp|swf|flv|mp4|ico)$ {
  #允许静态资源跨域请求
  add_header 'Access-Control-Allow-Origin' '*';
  add_header 'Access-Control-Allow-Credentials' 'true';
  add_header 'Access-Control-Allow-Methods' 'GET,POST,OPTIONS';
  add_header 'Access-Control-Allow-Headers' 'Origin, X-Requested-With, Content-Type, Accept, token, platform';
  expires 30d;
  access_log off;
}

```

3.宝塔删除PHP禁用函数  

```
putenv 
shell_exec 
proc_open 
pcntl_alarm 
pcntl_fork 
pcntl_wait 
pcntl_signal
pcntl_signal_dispatch
```
5.PHP安装拓展  
php拓展安装  
如果您使用的是宝塔，则可跳过此步骤。 因为宝塔默认已开启这两个拓展  
```
pcnt
posix
```


4.系统后台  

H5端地址：https://你的域名/h5  



5.如果runtime目录不存在，需要创建，并设置owner是www,权限是755  


