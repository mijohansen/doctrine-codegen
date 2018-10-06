<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 25/09/2018
 * Time: 00:02
 */

namespace DbUtil;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use PDO;

abstract class DbBase {

    const CONF_DSN = 'dsn';
    const CONF_USER = 'user';
    const CONF_PASS = 'pass';

    /**
     * @return PDO
     */
    static function pdo() {
        static $db;
        if (is_null($db)) {
            $config = static::getConfig();
            $dsn = $config[self::CONF_DSN];
            $user = $config[self::CONF_USER];
            $pass = $config[self::CONF_PASS];
            $db = new PDO($dsn, $user, $pass, array(
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL"',
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => FALSE
            ));
        }

        return $db;
    }

    static function getConfig() {
        return [
            self::CONF_DSN => null,
            self::CONF_USER => null,
            self::CONF_PASS => null,
        ];
    }

    static function getConnection() {
        static $connection;
        if (is_null($connection)) {
            $connectionParams = array(
                "pdo" => static::pdo()
            );
            $config = new Configuration();
            $connection = DriverManager::getConnection($connectionParams, $config);
        }
        return $connection;

    }

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function createQueryBuilder() {
        $connection = static::getConnection();
        return $connection->createQueryBuilder();
    }

    static function insert($table, $arrayofarrays) {
        $values = array();
        foreach ($arrayofarrays as $index => $dataobject) {
            foreach ($dataobject as $key => $value) {
                $dataobject[$key] = static::pdo()->quote($value);
            }
            $values[] = "(" . implode(",", $dataobject) . ")";
        }

        $query = implode(" " . PHP_EOL, array(
            "INSERT IGNORE INTO $table",
            "(" . implode(",", array_keys($arrayofarrays[0])) . ")",
            "VALUES", implode("," . PHP_EOL, $values)
        ));
        return self::pdo()->exec($query);
    }

    static function select($from, $where = [], $execute = false) {
        $builder = static::createQueryBuilder();
        $builder->from($from);
        $builder->select("*");
        foreach ($where as $key => $val) {
            $builder->where($key . " = " . $builder->createNamedParameter($val));
        }
        if ($execute) {
            $res = $builder->execute();
            if ($res) {
                return $res->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return false;
            }
        } else {
            return $builder;
        }
    }

}