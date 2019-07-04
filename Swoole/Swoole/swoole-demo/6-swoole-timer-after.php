<?php

// 场景：5秒后输出 123
swoole_timer_after(5 * 1000, function() {
    echo 123;
});
