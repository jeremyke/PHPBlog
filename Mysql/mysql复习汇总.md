 #### 1.MySQL的复制原理以及流程
 
 基本原理流程，3个线程以及之间的关联:
 ```text
1.binlog输出线程:每当有从库连接到主库的时候，主库都会创建一个线程然后发送binlog内容到从库。在从库里，当复制开始的时候，从库就会创建两个线程进行处理：
2.从库I/O线程:当START SLAVE语句在从库开始执行之后，从库创建一个I/O线程，该线程连接到主库并请求主库发送binlog里面的更新记录到从库上。从库I/O线程读取主库的binlog输出线程发送的更新并拷贝这些更新到本地文件，其中包括relay log文件。
3.从库的SQL线程:从库创建一个SQL线程，这个线程读取从库I/O线程写到relay log的更新事件并执行。
 ```
 #### 2.MySQL中myisam与innodb的区别
 
 |不同点|myisam|innodb|
 |:----    |:---:|:-----:|
 |（1）存储方式|数据和索引是分开存储的（3个文件（frm、MYD、MYI））|数据和索引是一起存储的（共享表空间存储和多表空间存储两种方式），2个存储文件（.ibd，.frm）|
 |（2）存储顺序|插入顺序|主键顺序|
 |（3）空间碎片的产生|会产生，需要定时清理（optimize table 表名）|不会产生|
 |（4）事务和外键约束|不支持|支持|
 |（5）锁级别|表锁|行锁|
 |（5）读写插入|快速|更新删除快速|
 
 #### 3.MySQL中字段类型
 
 - varchar(50)中50的涵义
 
 首先明确：mysql中UTF-8编码,汉字字符占3个字节，英文字符占1个字节。这里50表示最多存放50个字符，varchar(50)和(200)存储hello所占空间一样，但后者在排序时会消耗更多内存，因为order by col采用fixed_length计算col长度(memory引擎也一样)
 
 - int（20）中20的涵义
 
 首先明确int类型只能占用4个字节的存储空间，这里20是指最大显示宽度，但是最大显示宽度为255。如果存储数据不够显示宽度，设置UNSIGNED ZEROFILL(无符号）就会在数据左侧用0来填充位数。
 
  #### 4.MySQL事物的4种隔离级别
  
  - 日志
  
  - 隔离级别
  ```text
  读未提交(RU)
  读已提交(RC)
  可重复读(RR)
  串行
```
 - 事务是如何通过日志来实现的
 ```text
  事务日志是通过redo日志和innodb的存储引擎日志缓冲（Innodb log buffer）来实现的，当开始一个事务的时候，会记录该事务的lsn(log sequence number)号;
  当事务执行时，会往InnoDB存储引擎的日志的日志缓存里面插入事务日志；当事务提交时，必须将存储引擎的日志缓冲写入磁盘（通过innodb_flush_log_at_trx_commit来控制），
  也就是写数据前，需要先写日志。这种方式称为“预写日志方式”
 ``` 
 #### 5.MySQL数据库cpu飙升到500%的话他怎么处理？
 ```text
 1、列出所有进程  show processlist,观察所有进程 ,多秒没有状态变化的(干掉)
 2、查看超时日志或者错误日志 ,一般会是查询以及大批量的插入会导致cpu与i/o上涨,当然不排除网络状态突然断了,导致一个请求服务
 器只接受到一半，比如where子句或分页子句没有发送,当然的一次被坑经历.
 ```
 ##### 6.超键、候选键、主键、外键分别是什么？
 ```text

1、超键：在关系中能唯一标识元组的属性集称为关系模式的超键。一个属性可以为作为一个超键，多个属性组合在一起也可以作为一个超键。超键包含候选键和主键。
2、候选键：是最小超键，即没有冗余元素的超键。
3、主键：数据库表中对储存数据对象予以唯一和完整标识的数据列或属性的组合。一个数据列只能有一个主键，且主键的取值不能缺失，即不能为空值（Null）。
4、外键：在一个表中存在的另一个表的主键称此表的外键。
```
 ##### 7.mysql数据实时同步到Elasticsearch
 ```text
记录mysql的binlog日志，再执行ES document api，将数据同步到ES集群中。
mypipe同步数据到ES集群使用注意：
    1. mysql binlog必须是ROW模式
    2. 要赋予用于连接mysql的账户REPLICATION权限
       GRANT REPLICATION SLAVE, REPLICATION CLIENT ON *.* TO 'elastic'@'%' IDENTIFIED BY 'Elastic_123'
    3. mypipe只是将binlog日志内容解析后编码成Avro格式推送到kafka broker, 并不是将数据推送到kafka，如果需要同步到ES集群，可以从kafka消费数据后，再写入ES
    4. 消费kafka中的消息(mysql insert, update, delete操作及具体的数据)，需要对消息内容进行Avro解析，获取到对应的数据操作内容，进行下一步处理；mypipe封装了一个KafkaGenericMutationAvroConsumer类，可以直接继承该类使用，或者自行解析
    5. mypipe只支持binlog同步，不支持存量数据同步，也即mypipe程序启动后无法对mysql中已经存在的数据进行同步
mypipe同步数据到ES集群：
    mypipe将数据binlog event发送到kafka,再写一个消费方法，将mypipe推送到kafka的消息消费掉。

```
 