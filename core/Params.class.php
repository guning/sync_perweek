<?php
/**
 * todo:
 * User: guning
 * DateTime: 2017-11-13 10:22
 */
namespace core;

class Params
{
    public static function getParams($paramIndex = []) {
        global $argv;
        if (empty($paramIndex)) {
            return $argv;
        }
        return self::format($paramIndex, $argv);
    }
    private static function format($paramIndex, $argv) {
        $res = [];
        foreach ($paramIndex as $k => $v) {
            if (isset($argv[$k+1])) {
                $res[$v] = $argv[$k + 1];
            } else {
                throw new \Exception('ERROR : missing param ' . $k);
            }
        }
        return $res;
    }
}