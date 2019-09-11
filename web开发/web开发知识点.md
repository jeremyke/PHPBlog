 ## Cookie 和 Session
 ```text
 Cookie:服务器端生成，储存在用户浏览器端，用于记录web用户的基本信息。
 Session：服务器端生成，储存在服务器端，而本地浏览器会存储一个与服务器中session文件对应的Cookie值。Session 对象存储特定用户会话所需的属性及配置信息。
 
 COOKIE和SESSION的区别：
    （1）存储位置：Cookie存储在客户端浏览器中，相对不安全；Session内容所在文件存储在服务器中，一般在根目录下的tmp文件夹中，相对更安全。
    （2）数量和大小限制：Cookie存储的数据在不同的浏览器会有不同的限制，一般在同一个域名下，Cookie变量数量控制在20个以内，每个cookie值的大
 小控制在4kb以内。session值没有大小和数量限制，但如果数量过多，会增大服务器的压力。
    （3）内容区别：cookie保存的内容是字符串，而服务器中的session保存的数据是对象。
    （4）路径区别：session不能区分路径，同一个用户在访问一个网站期间，所有的session在任何一个地方都可以访问到；而cookie中如果设置了路径参数，
 那么同一个网站中不同路径下的cookie互相是访问不到的。
 
 用户禁用cookie如何使用session?
    方法一：php.ini文件设置 session.use_trans_sid = 1，这样他会在每个url后面自动加上PHPSESSID的值，然后正常使用session就可以了。
    方法二：保存session_id的值于数据库或redis中，然后在下一次要调用session前，运行session_id（$session_id），
 还有这条语句要在session_start()前。
 
 ```
 
 ## POST和GET区别
 ```text
 （1）传输内容大小：
     POST 根据php.ini文件配置，默认8M
     GET 2K
 （2）提交方式：
     POST 放在请求空白行中提交的
     GET 追加在URL上面的
 （3）安全性：
     POST 更安全
 ```