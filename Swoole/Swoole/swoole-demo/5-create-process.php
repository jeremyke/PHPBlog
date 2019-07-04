<?php

// 修改当前这个进程的名字为 phpprocess，修改当前进程的名字是由swoole提供
swoole_set_process_name('master-process');

// 创建一个子进程，这个子进程还未启动
$worker = new swoole_process(function() {
    // ，把这个子进程的名字设置为 worker-process
    swoole_set_process_name('worker-process');
    // 表示这个子进程创建成功后，需要执行的代码
    // 比如进行大量的计算
    sleep(10);
});

// 启动这个子进程
$worker->start();

// 在这里等待子进程执行完成后再退出主进程
swoole_process::wait();
