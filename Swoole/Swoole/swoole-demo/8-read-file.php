<?php

// echo file_get_contents('1.txt');
swoole_async_readfile('1.txt', function($filename, $content) {
    echo $content . "\r\n";
});

echo "\r\n";

echo "123\r\n";
