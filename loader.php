<?php
/**
 * todo:
 * User: guning
 * DateTime: 2017-11-13 11:14
 */
spl_autoload_register('__autoload__');
function __autoload__($funName) {
    $file = APP_ROOT . $funName . '.class.php';
    if (file_exists($file)) {
        require $file;
    }
}
