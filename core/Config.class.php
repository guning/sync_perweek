<?php
/**
 * todo:
 * User: guning
 * DateTime: 2017-11-13 10:34
 */

namespace core;


class Config
{
    public static function getConfig() {
        $config = [];
        $dirHandle = opendir(APP_ROOT . 'config');
        while ($file = readdir($dirHandle)) {
            if ($file !== '.' && $file !== '..')
            $tmp = include(APP_ROOT . 'config' . SLASH . $file);
            if (!empty($config)) {
                $config = @array_merge($config, $tmp);
            } else {
                $config = @array_merge(array(), $tmp);
            }
        }
        return $config;
    }
}