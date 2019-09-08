 ## 1. 安装JDK
 
 #### 1.1 下载
 jenkins依赖java环境。目前使用的是jdk 11

 jdk下载网页：https://www.oracle.com/technetwork/java/javase/downloads/jdk11-downloads-5066655.html

 下载：jdk-11.0.2_linux-x64_bin.tar.gz

 mkdir -p /alidata/server/

 解压缩:

 tar -zxvf jdk-11.0.2_linux-x64_bin.tar.gz -C /alidata/server/


 #### 1.2 配置环境变量

 vi ~/.bash_profile 修改配置文件，加入下面配置

 JAVA_HOME=/alidata/server/jdk-11.0.2/
 
 PATH=$PATH:$JAVA_HOME/bin

 ####让配置立即生效

 source ~/.bash_profile

 ## 2.部署tomcat
 
 #### 2.1 下载
 
 下载地址：https://tomcat.apache.org/download-90.cgi

 目前安装版本：tomcat 9

 下载：apache-tomcat-9.0.17.tar.gz

 解压缩: tar -zxvf apache-tomcat-9.0.17.tar.gz -C /alidata/server/


 ## 3.通过tomcat部署jenkins
 
 jenkins下载地址:  https://jenkins.io/zh/download/ 下载war版本即可

 目前安装版本：jenkins-2.164.1.war

 #### 3.1安装命令：

 mkdir -p /alidata/server/jenkins/

 cd /alidata/server/jenkins/

 wget -c "https://mirrors.tuna.tsinghua.edu.cn/jenkins/war-stable/2.176.2/jenkins.war"

 rm -rf /alidata/server/apache-tomcat-9.0.17/webapps/ROOT

 rm -rf /alidata/server/apache-tomcat-9.0.17/webapps/ROOT.war

 ln -sf  /alidata/server/jenkins/jenkins.war /alidata/server/apache-tomcat-9.0.17/webapps/ROOT.war

 #### 3.2启动/关闭tomcat

 /alidata/server/apache-tomcat-9.0.17/bin/startup.sh

 /alidata/server/apache-tomcat-9.0.17/bin/shutdown.sh

 tomcat默认端口: 8080

 使用ip + 8080端口即可访问，如果想使用域名访问可以通过nginx做http代理服务器。


 #### 3.3 注意：

 因为线上服务器都是使用www用户，为统一jenkins处理源码的权限，jenkins和tomcat也使用www用户运行。

 首先创建www用户和组

 groupadd www
 useradd -M -g www www

 #### 3.4启动tomcat

 - 使用www用户启动tomcat

 su - www -c '/alidata/server/apache-tomcat-9.0.17/bin/startup.sh'


 #### 3.5 开机启动配置：

 修改/etc/rc.local配置文件，将上面启动命令写进去。

 ##4.jenkins安装说明
 
 jenkins启动后，默认会在当前运行用户的主目录下，创建一个.jenkins 目录作为jenkins的数据目录。

 因为jenkins是使用www用户运行，因此jenkins的数据目录为：/home/www/.jenkins/

 jenkins job工作空间根目录：/home/www/.jenkins/workspace/

 每一个jenkins的job都会在workspace目录中创建一个子目录作为job的工作空间，job工作空间目录以job的名字命名。

 ##5.jenkins服务器依赖环境
 jenkins构建项目需要依赖各种命令，因此jenkins服务器上需要安装这些依赖的命令。

 目前依赖命令：

 docker
 helm
 kubectl - kubernetes客户端，只需要客户端，不需要安装k8s
 dos2unix - 用于将windows文本转换成unix格式

 安装dos2unix

 yum install dos2unix

 安装docker, 参考文档：docker基础教程

 helm和kubectl目前需要翻墙下载，目前安装在/alidata/server/目录中。

 helm官网：https://helm.sh/

 kubectl客户端安装：https://kubernetes.io/docs/tasks/tools/install-kubectl/



