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