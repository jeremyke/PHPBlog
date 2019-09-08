
安装目录: /opt/Atlassian

启动wiki:

cd /opt/atlassian/confluence/bin

./start-confluence.sh



关闭wiki:

cd /opt/atlassian/confluence/bin

./stop-confluence.sh



因为禅道和wiki同时安装在一台服务器上，为了能够共用80端口，所以wiki使用nginx作为代理服务器。

wiki的nginx配置文件：/alidata/server/nginx/conf/vhosts/wiki.conf

```shell
server {
    listen 80;
    set $host_path "/alidata/www/default";
    access_log /alidata/log/nginx/access/wiki-access.log;
    error_log  /alidata/log/nginx/error/wiki-error.log;

    server_name  wiki.xunjoy.com;
    root   $host_path;
    set $yii_bootstrap "index.php";

    charset utf-8;
    proxy_connect_timeout       3000;
    proxy_send_timeout          3000;
    proxy_read_timeout          3000;
    send_timeout                3000;

    location / {
        proxy_pass http://127.0.0.1:8090;
    }

    # prevent nginx from serving dotfiles (.htaccess, .svn, .git, etc.)
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

wiki使用的是8090端口，nginx接收到wiki.xunjoy.com域名的请求的时候，将http请求转发给wiki处理。



安装wiki

官网：https://www.atlassian.com/software/confluence

从官网下载安装程序后，给安装程序可执行权限。

例如：

chmod u+x  /root/atlassian-confluence-6.15.2-x64.bin

#执行安装程序

/root/atlassian-confluence-6.15.2-x64.bin

根据引导一步一步设置即可完成安装，都选择默认也可以完成安装。



安装完成后，需要访问wiki，进行初始化，这里需要设置数据库，管理帐号密码。