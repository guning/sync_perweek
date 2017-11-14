<?php
/**
 * todo:
 * User: guning
 * DateTime: 2017-11-13 14:02
 */

namespace core;
use core\Config;

class DBadn
{
    private static $conn = null;
    private static $instance = null;

    public static function getInstance($config = []) {
        if (empty(self::$instance)) {
            if (empty($config) || !is_array($config)) {
                $configs = Config::getConfig();
                $config = $configs['adn'];
            }
            self::$instance = new DBadn($config);
        }
        return self::$instance;
    }

    private function __construct($config)
    {
        if (!empty($config)) {
            $dsn = $config['dbms'] . ':host=' . $config['host'] . ';port=' .$config['port'] . ';dbname=' . $config['dbname'];
            $user = $config['user'];
            $pwd = $config['pwd'];
            try {
                self::$conn = new \PDO($dsn, $user, $pwd);
            } catch (\PDOException $e) {
                echo 'Connection failed ' . json_encode($e->getMessage()) . "\n";
                exit;
            }
        } else {
            throw new \Exception('DB CONNECT ERROR:CONFIG IS NULL');
        }
    }

    public function query($sql) {
        $res = self::$conn->query($sql);
        $data = array();
        if ($res !== false) {
            foreach ($res as $row) {
                foreach ($row as $key => $value) {
                    if (is_int($key)) {
                        unset($row[$key]);
                    }
                }
                $data[] = $row;
            }
            return $data;
        } else {
            throw new \Exception('GET DBDATA ERROR');
        }
    }
}