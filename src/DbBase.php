<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 25/09/2018
 * Time: 00:02
 */

namespace DbUtil;

use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;

abstract class DbBase {

    /**
     * @return PDO
     */
    static function pdo() {
        static $db;
        static $config;
        if(is_null($config)){
            $config = static::getConfig();
        }
        if (is_null($db)) {
            $db = new \PDO(
                $config->getDsn(),
                $config->getUsername(),
                $config->getPasswd(),
                array(
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="TRADITIONAL", time_zone = "+00:00"',
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => FALSE
            ));
        }

        return $db;
    }

    /**
     * @return DbConfig
     */
     abstract static function getConfig();

    static function shortFieldName($long_field_name) {
        $parts = explode(".", $long_field_name);
        return array_pop($parts);
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

    static function insertMany($table, $arrayofarrays) {
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

    static function insert($table, $values) {
        $builder = self::createQueryBuilder();
        $builder->insert($table);
        foreach ($values as $key => $val) {
            if (is_bool($val)) {
                $val = $val ? 1 : 0;
            }
            $builder->setValue($key, $builder->createNamedParameter($val));
        }
        if ($builder->execute()) {
            return $builder->getConnection()->lastInsertId();
        } else {
            return false;
        }
    }

    static function update($table, $data, $where) {
        $builder = self::createQueryBuilder();
        $builder->update($table);
        foreach ($data as $key => $val) {
            if (is_bool($val)) {
                $val = $val ? 1 : 0;
            }
            $builder->set($key, $builder->createNamedParameter($val));
        }
        foreach ($where as $key => $val) {
            $builder->where($key . " = " . $builder->createNamedParameter($val));
        }
        if ($builder->execute()) {
            return true;
        } else {
            return false;
        }
    }

    static function delete($table, $where) {

    }

    /**
     * @param QueryBuilder $builder
     * @param $field
     * @param $value
     * @return QueryBuilder
     */
    static function where(QueryBuilder $builder, $field, $value) {
        $builder->where($field . "=" . $builder->createNamedParameter($value));
        return $builder;
    }

    static function execute(QueryBuilder $builder, $model_object) {
        $select_fields = [];
        $fields = [];
        foreach ($model_object as $alias => $statement) {
            $select_fields[] = "$statement AS $alias";
            $fields[] = $alias;
        }
        $model_class_name = get_class($model_object);
        $builder->select($select_fields);
        $res = $builder->execute();
        $output = [];
        foreach ($res->fetchAll() as $row) {
            $result_model = new $model_class_name();
            foreach ($fields as $field) {
                $result_model->$field = $row[$field];
            }
            $output[] = $result_model;
        }
        return $output;
    }

    static function camelize($fieldName) {
        $fieldName = explode(".", $fieldName);
        $fieldName = array_pop($fieldName);
        $fieldName = Inflector::camelize($fieldName);
        return $fieldName;
    }
    static function camelizeRow($row){
        $obj = new \stdClass();
        foreach ($row as $field => $value){
            $fieldName = self::camelize($field);
            $obj->$fieldName = $value;
        }
        return $obj;
    }

    static function createPathWithKeys($basepath, $keys) {
        foreach ($keys as $i => $fieldName) {
            $fieldName = DbBase::camelize($fieldName);
            $keys[$i] = "{" . $fieldName . "}";
        }
        array_unshift($keys, $basepath);
        return implode("/", $keys);
    }

}