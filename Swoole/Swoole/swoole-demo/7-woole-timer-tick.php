<?php

swoole_timer_tick(5 * 1000, function() {
    echo 123 . "\r\n";
});
