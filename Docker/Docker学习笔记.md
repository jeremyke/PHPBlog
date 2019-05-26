 ## 1. Docker实践
 container 是建立在image层之上“只读”
 
 ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/1739022581121130.png)

 **Docker镜像和容器常用命令：** 
 - docker image ls
 - docker image rm [镜像id]
 - docker container ls -a
 - docker container ls -aq(列出所有容器的id)
 - docker container rm [容器id]
 - docker container rm $(docker container ls -aq)(删除所有容器)
 - docker container rm $(docker container ls f="status=exit" -q)(删除所有退出的容器)

 **docker commit命令**
 > 把一个退出状态的container 打包成一个新的image
 ```
 	完整写法： docker container commit
 ``` 

  **docker built命令**
  > 通过Dockerfile创建一个镜像
  

  **Dockerfile的写法：**
  ```
  dockerfile有2种写法如下图：
  ```
   ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/18280510243315.png)
  
   - FROM 
   >基于的镜像名称，例如：FROM nginx:v16.9，初始的镜像（base image就FROM scratch）
   
   - LABLE 
   >maintainer,version,description(Metadata不可少)
   
   - RUN 	
   >执行的命令)（这里有个问题：镜像是只读的，如果操作中带有写操作为什么不报错呢？是因为：RUN的时候会生成临时的Container（可读写）,等执行完了之后就退出了）（为了美观，用反斜线换行\，为了避免层次太多，合并多条命令成一行&&）
  WORKDIR（设定当前目录，没有你指定的目录，会自动创建。尽量使用绝对目录）
  
   - ADD 和 COPY:
   >ADD 【某个目录】 【目标目录】（ADD比COPY强于能自动解压）,大部分情况，COPY优于ADD,添加远程目录请使用curl或者wget
   
   - ENV
   >(设置环境变量) ENV MYSQL_VERSION 5.6
   
   - VOLUME(存储)
   - EXPOSE(网络)
   - CMD：
   >设置容易启动后默认执行的命令和参数；<br/>
   如果docker run指定了其他命令，CMD命令会被忽略；<br/>
   如果定义多个CMD，只有最后一个会执行
   - ENTERPOINT：
   >设置容器启动时运行的命令；<br/>
   让容器以应用程序或者服务的形式运行；<br/>
   不会被忽略，一定会执行；<br/>
   可以写一个shell脚本作为enterpoint:例如：ENTERPOINT ['hello.sh']
        
  
  **镜像发布：**
  >这里发布到docker hub忽略不说，重点说下实践工作中发布到远程服务器的例子。
   - 第一步：
   
   在目标服务器运行一个container,(docker run -d -p 5000:5000 --restart always --name registory registory:2)

   - 第二步：
   
   本地代码push到远程容器（build镜像的时候：docker build -t 192.168.12.45:5000/helloworld .） 
   ```
   注意：为了说明本地环境push代码是安全的的，需要做以下事情：
   （1）ls /etc/docker 创建daemon.json文件 内容是：{"insecure-registeries":["192.168.12.45:5000"]}
   （2）vim /lib/systemed/system/docker.service 加一行
   在ExecStart=/usr/bin/docker下面加入：EnvironmentFile=-/etc/docker/daemon.json
   （3）service docker restart
   ```  
   - 第三步：
   
   docker push 192.168.12.45:5000/helloworld
   
   **容器的操作**
   ```
   Docker run -it [镜像名称] /bin/bash 交互式的运行某个镜像并且进入镜像里面
   Docker run -d [镜像名称] 在后台执行某个container
   ```
   - docker exec -it [containerID] /bin/bash（还可以运行其他命令） 
   >进入一个运行中的container里面
   
   - docker inspect [containerID] 显示docker详细信息
   
   
  ## 2.Docker网络
  ```
          |-bridge network
      单机-|-host network
          |-none network 
          
      多机-overlay network
  ```
  
  #### 2.1 网络基础
  
  > 介绍一个网络抓包工具：wireshark
  
   - 网络的2种模型
   
   ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/17480126494239.png)
   
   - NAT
   
   ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/16780630345924.png)
   
   
  #### 2.2 Linux网络命名空间
  
  示意图
  
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/173606258412577.png)
  
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/18141217427864.png)
  
  > 每个容器的network namespace都是相互隔离开的，和宿主主机也是隔离开的。
  
  **实验演示**
  ```
  第一步：在宿主主机上创建2个netns:sudo ip netns add test1；sudo ip netns add test2；通过命令sudo ip netns list查看netns
  第二步：查看2个netns的ip地址 sudo ip netns exec test1 ip a 
  第三步：查看2个netns的ip link sudo ip netns exec test1 ip link
  第四步：将test1的ip 状态up起来：sudo ip netns exec test1 ip link set dev lo up(状态变为unknow,因为一个端口是否起来
  需要连接另一个进行测试，单个端口状态是unknow的)
  第五步：在宿主主机添加一对ip link：sudo ip link add veth-test1 type veth peer name  veth-test2
  第六步：把veth-test1和veth-test2 分别添加到netns test1和test2：sudo ip link set veth-test1 netns test1
  第七步：给2个netns配置ip地址：sudo ip netns exec test1 ip addr add 192.168.1.1/24 dev veth-test1
  第八步：将2个netns Up  起来：sudo ip netns exec test1 ip link set dev veth-test1[2] up
  第九步：查看2个netns的ip地址：sudo ip netns test1 ip a
  第十步：在其中一个netns中ping另外一个netns的ip发现是通的：sudo ip netns exec test1 ping 192.168.1.2
  ```
  
  #### 2.3 Docker Bridge
  >查看docker网络:docker network ls
  
  **一张图弄清楚2个container如何通信和单个container如何访问外网**
  
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/17731208708661.png)
    
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/1700101689119104.png)
  
  #### 2.3 Docker间的通信
  
   - 先创建一个容器
   
 sudo docker run -d --name test1busybox /bin/sh -c "while true;do sleep 3600;done"
  - 再创建另一个容器 link 之前那个容器，这样2个容器就连接起来的，test2可以直接ping test1这个容器名字
 
 sudo docker run -d --name test2 --link test1 busybox /bin/sh -c "while true;do sleep 3600;done"
 
 >创建一个container会默认连接到bridge这个容器，其实可以连接到已有或者自己新建的network
 
 实验步骤：
 ```
 第一步：创建自己的network:sudo docker network create -d bridge my-bridge
 第二步：新创建一个container连接到这个my-bridge：sudo docker run -d --name test3 -- network my-bridge busybox /bin/sh -c 
 "while true;do sleep 3600;done"
 ```
  - 默认的bridge和自定义的bridge区别：
  ```
  2个容器连接到默认的bridge，2者可以通过ping ip连接无法通过ping 名称连接，只有link了之后才可以单向的ping 名字；
  但是如果2个容器都连接到自定义的bridge之后，相互之间可以通过ping 名称连接。
  ```
  
  #### 2.3 Docker 端口映射
  
  **网路图**
  
  ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/20190520172031.png)
  
  **命令**
  
  docker run --name web -d -p 8000(宿主机的端口):8000(容器的端口) nginx
  
 #### 2.4 Docker 网络之host和none
 
 docker网络有3种：
 
 ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/167510217510269.png)
 
 >创建容器的时候指定连接的network:<br/>
 docker run -d -name test1 --network none[host] busybox /bin/sh -c "while true;do sleep 3600;done"<br/>
 说明：连接到none的容器，没有IP地址，是个孤立的，外部无法访问；连接到host的容器，和宿主主机公用一套network，
 这个时候需要主要端口冲突。
 
  #### 2.5 多机通信overlay && underlay
  >看图：
  
   ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/180908117510798.png)
   
   
 ## 3. Docker持久化存储和数据共享
 
 docker volume图解：
 
 ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/17290503160940.png)
 
 Docker持久化方案：
 
 ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/16751028362736.png)
 
 >volume类型：<br/>
 (1)受管理的data volume,由docker后台自动创建<br/>
 (2)绑定挂载的volume，挂载位置由用户指定.
 
 
 #### 3.1 持久化方案一.data volume
 
  - Dockerfile指定路径
  
  VOLUME["/var/lib/mysql"]
 
  - 命名
  
  docker run -d -v mysql:/var/lib/mysql(说明：volume名称:volume在宿主机的地址) --name mysql1 MYSQL_ALLOW_EMPTY_PASSWORD
 =true mysql
 
  - 特点
  
  container被删除之后，该volume不会被删除，下一次创建container如果仍然指定这个volume，可以继续使用，而且原来的数据仍然在这里.
  
  #### 3.2 持久化方案二.Bind Mouting
  
  **提示：**用这个方法将本地的项目文件映射到container里面去，对于本地使用docker开发简直牛逼的不行!
  
  >无需再DockerFile里面指定volume地址，只需在构建容器的时候通过-v参数指定宿主机路径和容器路径相映射
  
  - 命令
  
  docker run -d -v $(pwd):usr/share/nginx/html -p 80:80 --name mysql1 MYSQL_ALLOW_EMPTY_PASSWORD
   =true mysql
   
  - 特点
  
  在宿主机或者容器相应的位置修改文件，在对方目录都会同步，其实就是同一个目录.
  
  sudo docker run --name nginx -p 8080:443 -v $(pwd):/var/www/html -d boxedcode/alpine-nginx-php-fpm
  
  
   ## 4. Docker composer
   
   #### 4.1 搭建一个WordPress 应用
   
   ```
   第一步：构建mysql容器<br/>
   docker run -d --name mysql -v mysql-data:/var/lib/mysql -e MYSQL_ROOT_PASSWORD=root -e MYSQL_DATABASE=wordpress mysql<br/>
   第二步：构建WordPress容器<br/>
   docker run -d -e WORDPRESS_DB_HOST=mysql:3306 --link mysql -p 8080:80 wordpress<br/>
   [注意有坑]：<br/>
   由于使用的是最新版的mysql 8.x 导致数据库连不上，需要做如下处理：
   docker exec -it mysql bash<br/>
   mysql -u root -pPASSWORD<br/>
   ALTER USER root IDENTIFIED WITH mysql_native_password BY 'PASSWORD';<br/>

   ```
   #### 4.1 什么是Docker composer
   >docker composer是一个根据yml文件管理多个容器的命令行工具
  
  **yml文件**
  ```
  默认名称是：docker-composer.yml
  三部分：
  （1）service:一个service代表一个container，这个container可以从dockerhub的image来创建，或者从本地dockerfile build的出来的image来创建。
       可以设置volumes和networks
   (2)volumes
   (3)networks

  ```
  
  如图案例：
  
   ![image](https://github.com/jeremyke/PHPBlog/blob/master/Pictures/docker-composer.png)
   
  **docker-compose安装**
  ```
  第一步：下载文件： curl -L https://github.com/docker/compose/releases/download/1.23.0-rc3/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
  第二步：修改权限：chmod +x /usr/local/bin/docker-compose

  ```
  **docker-compose常用命令**
  ```
  docker-compose (-f filename.yml指定的yml文件)up ：启动一个compose组
  docker-compose stop(down停止并且删除)只是停止
  docker-compose start 启动compose组
  docker-compose ps 查看service情况
  docker-compose image 查看镜像
  docker-compose exec service名 bash 进入某一个service容器

  ```
  **负载均衡的实现**
  >安装ockercloud/haproxy
  
  ```ssh
  version: "3"
  
  services:
  
    redis:
      image: redis
  
    web:
      build:
        context: .
        dockerfile: Dockerfile
      environment:
        REDIS_HOST: redis
  
    lb:
      image: dockercloud/haproxy
      links:
        - web
      ports:
        - 8080:80
      volumes:
        - /var/run/docker.sock:/var/run/docker.sock 
  
  ```
  >通过docker-compose up --scale web=n -d来实现
  