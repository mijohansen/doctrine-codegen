<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 06/10/2018
 * Time: 13:52
 */

namespace DbUtil;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class Utils {

    /**
     * @param $pdoCallableFunction
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    static function getConnectionBasedOnPdoFunction($pdoCallableFunction) {
        static $connection = [];
        if (is_null($connection[$pdoCallableFunction])) {
            $connectionParams = array(
                "pdo" => call_user_func($pdoCallableFunction)
            );
            $config = new Configuration();
            $connection[$pdoCallableFunction] = DriverManager::getConnection($connectionParams, $config);
        }
        return $connection[$pdoCallableFunction];
    }

    /**
     * @param $className
     * @return array
     * @throws \ReflectionException
     */
    static function getFields($className) {
        $class = new \ReflectionClass($className);
        $props = $class->getConstants();
        return array_values($props);
    }

}