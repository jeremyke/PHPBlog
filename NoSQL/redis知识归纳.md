## 1.特点
```text
支持数据持久化，可将内存中的数据保存在磁盘，重启时再次加载
支持 KV 类型数据，也支持其他丰富的数据结构存储
支持数据备份，即 master-slave 模式的数据备份
```
## 2.支持哪些数据结构
```text
STRING：字符串、整数或浮点数
    常用命令：set/get/decr/incr/mget 等；
    应用场景：String 是最常用的一种数据类型，普通的 key/value 存储都可以归为此类；
    实现方式：String 在 redis 内部存储默认就是一个字符串，被 redisObject 所引用，当遇到 incr、decr 等操作时会转成数值型进行计算，此时 redisObject 的 encoding 字段为 int。

LIST：列表，可存储多个相同的字符串
    常用命令：lpush/rpush/lpop/rpop/lrange 等；
    应用场景：Redis list 的应用场景非常多，也是 Redis 最重要的数据结构之一，比如 twitter 的关注列表，粉丝列表等都可以用 Redis 的 list 结构来实现；消息队列；
    实现方式：Redis list 的实现为一个双向链表，即可以支持反向查找和遍历，更方便操作，不过带来了部分额外的内存开销，Redis 内部的很多实现，包括发送缓冲队列等也都是用的这个数据结构。
        
SET：集合，存储不同元素，无序排列
    常用命令：sadd/spop/smembers/sunion 等；
    应用场景：Redis set 对外提供的功能与 list 类似是一个列表的功能，特殊之处在于 set 是可以自动排重的，当你需要存储一个列表数据，又不希望出现重复数据时，
             set 是一个很好的选择，并且 set 提供了判断某个成员是否在一个 set 集合内的重要接口，这个也是 list 所不能提供的；
    实现方式：set 的内部实现是一个 value 永远为 null 的 HashMap，实际就是通过计算 hash 的方式来快速排重的，这也是 set 能提供判断一个成员是否在集合内的原因
        
HASH：散列表，存储键值对之间的映射，无序排列
    常用命令：hget/hset/hgetall 等
    应用场景：我们要存储一个用户信息对象数据，其中包括用户 ID、用户姓名、年龄和生日，通过用户 ID 我们希望获取该用户的姓名或者年龄或者生日；
    实现方式：Redis Hash 对应 Value 内部实际就是一个 HashMap，实际这里会有 2 种不同实现，这个 Hash 的成员比较少时 Redis 为了节省内存会采用类似
             一维数组的方式来紧凑存储，而不会采用真正的 HashMap 结构，对应的 value redisObject 的 encoding 为 zipmap, 当成员数量增大时会自动转成真正的 HashMap,
             此时 encoding 为 ht

ZSET：有序集合，存储键值对，有序排列
    常用命令：zadd/zrange/zrem/zcard 等；
    应用场景：Redis sorted set 的使用场景与 set 类似，区别是 set 不是自动有序的，而 sorted set 可以通过用户额外提供一个优先级 (score) 的参数来为成员排序，
             并且是插入有序的，即自动排序。当你需要一个有序的并且不重复的集合列表，那么可以选择 sorted set 数据结构，比如 twitter 的 public timeline 可以以发表时间作为 score 来
             存储，这样获取时就是自动按时间排好序的。
    实现方式：Redis sorted set 的内部使用 HashMap 和跳跃表 (SkipList) 来保证数据的存储和有序，HashMap 里放的是成员到 score 的映射，而跳跃表里存放的
             是所有的成员，排序依据是 HashMap 里存的 score, 使用跳跃表的结构可以获得比较高的查找效率，并且在实现上比较简单。

```
## 3.Redis 与 Memcache 区别

|对比项|Redis|Memcache|
|:----    |:---:|:-----:|
|存储方式|部份存在硬盘上，这样能保证数据的持久性|数据全部存在内存之中，断电后会挂掉，数据不能超过内存大小|
|数据结构|丰富数据类型|只支持简单 KV 数据类型|
|数据一致性|事务|cas|
|持久性|快照/AOF|不支持|
|网络IO|单线程 IO 复用|多线程、非阻塞 IO 复用|
|内存管理机制|现场申请内存|预分配内存|
 
## 4.持久化策略
 
#### 4.1 RDB快照持久化
>将某一时刻的所有数据写入硬盘。使用BGSAVE命令，随着内存使用量的增加，执行 BGSAVE 可能会导致系统长时间地停顿

- 触发方式
```text
(1)使用bgsave命令手动触发
(2)自动触发(根据配置文件)

```
- 执行过程
```text
当 Redis 需要保存 dump.rdb 文件时，服务器执行以下操作：
1. Redis 调用 fork() ，同时拥有父进程和子进程。
2. 子进程将数据集写入到一个临时 RDB 文件中。
3. 当子进程完成对新 RDB 文件的写入时，Redis 用新 RDB 文件替换原来的 RDB 文件，并删除旧的 RDB 文件。
```
- 配置(redis.conf)
```bash
dbfilename  dump.rdb # 快照持久化备份方案
dir ./ # 快照持久化保存位置
save 900 1 # 900秒内如果超过1个key被修改, 则发起快照

```
- 优缺点

优点：
````text
RDB 是一个非常紧凑（compact）的文件，它保存了 Redis 在某个时间点上的数据集。 
这种文件非常适合用于进行备份：比如说，你可以在最近的24小时内，每小时备份一次RDB文件，并且在每个月的每一天，也备份一个RDB文件。 
这样的话，即使遇上问题，也可以随时将数据集还原到不同的版本。

RDB 可以最大化 Redis 的性能：父进程在保存 RDB 文件时唯一要做的就是 fork 出一个子进程，
然后这个子进程就会处理接下来的所有保存工作，父进程无须执行任何磁盘 I/O 操作。

RDB 在恢复大数据集时的速度比 AOF 的恢复速度要快。
````
缺点：
```text
如果你需要尽量避免在服务器故障时丢失数据，那么 RDB 不适合你。 
虽然 Redis 允许你设置不同的保存点（save point）来控制保存 RDB 文件的频率， 
但是， 因为 RDB 文件需要保存整个数据集的状态， 所以它并不是一个轻松的操作。 
因此你可能会至少 5 分钟才保存一次 RDB 文件。 在这种情况下， 一旦发生故障停机， 你就可能会丢失好几分钟的数据。
```
 
 
 
#### 4.2 AOF日志持久化
>AOF的运行原理是不断的将写入的命令以 Redis 通信协议的数据格式追加到 .aof 文件末尾只追加文件，在执行写命令时，命令复制到硬盘里面。使用 AOF 策略需要对硬盘进行大量写入，Redis 处理速度会受到硬盘性能的限制

- 触发方式
```text
（1）手动触发（使用bgrewriteaof命令）
    Redis主进程fork子进程来执行AOF重写，这个子进程创建新的AOF文件来存储重写结果，防止影响旧文件。因为fork采用了写时复制机制，子进程不能访问在其被创建出来之后产生的新数据。
    Redis使用“AOF重写缓冲区”保存这部分新数据，最后父进程将AOF重写缓冲区的数据写入新的AOF文件中然后使用新AOF文件替换老文件。
（2）自动触发（配置文件）
    appendonly：是否打开AOF持久化功能
    appendfilename：AOF文件名称
    appendfsync：同步频率
    auto-aof-rewrite-min-size：如果文件大小小于此值不会触发AOF，默认64MB
    auto-aof-rewrite-percentage：Redis记录最近的一次AOF操作的文件大小，如果当前AOF文件大小增长超过这个百分比则触发一次重写，默认100
```
- 执行过程
```text
1. Redis 执行 fork() ，现在同时拥有父进程和子进程。
2. 子进程创建新的新的AOP文件，把现有的AOP文件内容写入到新的AOP文件中。
3. 对于所有新执行的写入命令，父进程一边将它们累积到一个内存缓存中，一边将这些改动追加到现有AOF文件的末尾。这样即使在重写的中途发生停机，现有的 AOF 文件也还是安全的。
4. 当子进程完成重写工作时，它给父进程发送一个信号，父进程在接收到信号之后，将内存缓存中的所有数据追加到新 AOF 文件的末尾。
5. 现在 Redis 原子地用新文件替换旧文件，之后所有命令都会直接追加到新 AOF 文件的末尾。
```
- 持久化执行策略
```text
AOF 持久化方案提供 3 种不同时间策略将数据同步到磁盘中，同步策略通过 appendfsync 指令完成：
1. everysec（默认）：表示每秒执行一次 fsync 同步策略，效率上同 RDB 持久化差不多。由于每秒同步一次，所以服务器故障时会丢失 1 秒内的数据。
2. always: 每个写命令都会调用 fsync 进行数据同步，最安全但影响性能。
3. no: 表示 Redis 从不执行 fsync，数据将完全由内核控制写入磁盘。对于 Linux 系统来说，每 30 秒写入一次。
```
- 优缺点
```text
优点:
    1. 提供比 RDB 持久化方案更安全的数据，由于默认采用每秒进行持久化处理，
        所有即使服务器重启或宕机，最多也就丢失 1 秒内的数据。
    2. AOF 文件有序地保存了对数据库执行的所有写入操作， 这些写入操作以 Redis 协议的格式保存， 
        因此 AOF 文件的内容非常容易被人读懂， 对文件进行分析（parse）也很轻松。
缺点:
    1. 相比于 RDB 持久化，AOF 文件会比 RDB 备份文件大得多。
    2. AOF 持久化的速度可能比 RDB 持久化速度慢。
```


## 5. Redis 事务
```bash
redis> MULTI  #标记事务开始
OK
redis> INCR user_id  #多条命令按顺序入队
QUEUED
redis> INCR user_id
QUEUED
redis> INCR user_id
QUEUED
redis> PING
QUEUED
redis> EXEC  #执行
1) (integer) 1
2) (integer) 2
3) (integer) 3
4) PONG
```
注意：
在 Redis 事务中如果有某一条命令执行失败，其后的命令仍然会被继续执行
使用 DISCARD 可以取消事务，放弃执行事务块内的所有命令

## 6 分布式锁
#### 6.1 方式一
```bash
tryLock() {
    SETNX Key 1 Seconds
}
release() {
    DELETE Key
}
#缺陷：C1 执行时间过长未主动释放锁，C2 在 C1 的锁超时后获取到锁，C1 和 C2 都同时在执行，可能造成数据不一致等未知情况。
# 如果 C1 先执行完毕，则会释放 C2 的锁，此时可能导致另外一个 C3 获取到锁
```
#### 6.2 方式二
```bash
tryLock() {
    SETNX Key UnixTimestamp Seconds
}
release() {
    EVAL(
        #LuaScript
        if redis.call("get",KEYS[1] == ARGV[1])then
            return redis.call("del", KEYS[1])
        else
            return 0
        end
    )
}
#缺陷：极高并发场景下(如抢红包场景)，可能存在 UnixTimestamp 重复问题。分布式环境下物理时钟一致性，也无法保证，也可能存在 UnixTimestamp 重复问题
```
#### 6.3 方式三
```bash
tryLock() {
    SET Key UniqId Seconds
}
release() {
    EVAL (
        //LuaScript
        if redis.call("get", KEYS[1]) == ARGV[1] then
            return redis.call("del", KEYS[1])
        else
            return 0
        end
    )
}
#执行 SET key value NX 的效果等同于执行 SETNX key value。目前最优的分布式锁方案，但是如果在集群下依然存在问题。由于 Redis 集群数据同步为异步，
# 假设在 Master 节点获取到锁后未完成数据同步情况下 Master 节点 crash，在新的 Master 节点依然可以获取锁，所以多个 Client 同时获取到了锁
```
## 7. Redis 过期策略及内存淘汰机制

#### 7.1 过期策略
>Redis 的过期策略就是指当 Redis 中缓存的 Key 过期了，Redis 如何处理

```text
定时过期：每个设置过期时间的 Key 创建定时器，到过期时间立即清除。内存友好，CPU 不友好

惰性过期：访问 Key 时判断是否过期，过期则清除。CPU 友好，内存不友好

定期过期：隔一定时间，expires 字典中扫描一定数量的 Key，清除其中已过期的 Key。内存和 CPU 资源达到最优的平衡效果
```
#### 7.2 内存淘汰机制

```text
[root]# redis-cli config get maxmemory-policy
1) "maxmemory-policy"
2) "noeviction"
```
```text
noeviction：新写入操作会报错
allkeys-lru：移除最近最少使用的 key
allkeys-random：随机移除某些 key
volatile-lru：在设置了过期时间的键中，移除最近最少使用的 key
volatile-random：在设置了过期时间的键中，随机移除某些 key
volatile-ttl：在设置了过期时间的键中，有更早过期时间的 key 优先移除
```

## 8. Redis 的 7 个应用场景
```text
1. 缓存 —— 热数据：
    热点数据（经常会被查询，但是不经常被修改或者删除的数据）
2. 计数器： 统计点击数等应用
3. 队列： 相当于消息系统； 队列不仅可以把并发请求变成串行，并且还可以做队列或者栈使用
4. 位操作（大数据处理）：
    用于数据量上亿的场景下，例如几亿用户系统的签到，去重登录次数统计，某用户是否在线状态等等。
    想想一下腾讯 10 亿用户，要几个毫秒内查询到某个用户是否在线，你能怎么做？千万别说给每个用户建立一个 key，然后挨个记（你可以算一下需要的内存会很恐怖，而且这种类似的需求很多，腾讯光这个得多花多少钱。。）好吧。这里要用到位操作 —— 使用 setbit、getbit、bitcount 命令。
    原理是：redis 内构建一个足够长的数组，每个数组元素只能是 0 和 1 两个值，然后这个数组的下标 index 用来表示我们上面例子里面的用户 id（必须是数字哈），那么很显然，这个几亿长的大数组就能通过下标和元素值（0 和 1）来构建一个记忆系统，上面我说的几个场景也就能够实现。用到的命令是：setbit、getbit、bitcount
5. 分布式锁与单线程机制：
    验证前端的重复请求（可以自由扩展类似情况），可以通过 redis 进行过滤：每次请求将 request Ip、参数、接口等 hash 作为 key 存储 redis（幂等性请求），设置多长时间有效期，然后下次请求过来的时候先在 redis 中检索有没有这个 key，进而验证是不是一定时间内过来的重复提交
    秒杀系统，基于 redis 是单线程特征，防止出现数据库 “爆破”
    全局增量 ID 生成，类似 “秒杀”
6. 最新列表：
    例如新闻列表页面最新的新闻列表，如果总数量很大的情况下，尽量不要使用 select a from A limit 10 这种 low 货，尝试 redis 的 LPUSH 命令构建 List，
    一个个顺序都塞进去就可以啦。不过万一内存清掉了咋办？也简单，查询不到存储 key 的话，用 mysql 查询并且初始化一个 List 到 redis 中就好了。
7. 排行榜
    谁得分高谁排名往上。命令：ZADD（有续集，sorted set）
```

