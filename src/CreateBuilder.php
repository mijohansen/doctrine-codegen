<?php

namespace DbUtil;

use Doctrine\Common\Inflector\Inflector;

class CreateBuilder {

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function createQueryBuilderFromPdoString($pdoFunctionString) {
        $connection = Utils::getConnectionBasedOnPdoFunction($pdoFunctionString);
        return $connection->createQueryBuilder();
    }

    /**
     * @todo write test to logic
     * @param $fields
     * @return array
     */
    static function rearrangeFields($fields){
        if (is_array($fields)) {
            /**
             * Go through fields and write them out
             */
            foreach ($fields as $key => $val) {
                if (stripos($val, " as ")) {
                    // We should not touch this one...
                } elseif (!is_numeric($key)) {
                    // When key is set, the expression in value should be printed AS alias
                    $fields[$key] = $val . " AS `" . $key . "`";
                } else {
                    // Else just repeat the requestet field AS alias
                    $fields[$key] = $val . " AS `" . $val . "`";
                }
            }
            // remove keys
            $fields = array_values($fields);
        }
        return $fields;
    }
    /**
     * @param $tableClassName
     * @param $fields
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function select($tableClassName, $fields, $where=[]) {
        $tableName = $tableClassName::getTableName();
        $pdoString = $tableClassName::getPdoString();
        $builder = self::createQueryBuilderFromPdoString($pdoString);
        $builder->from($tableName, $tableName);
        if (is_null($fields)) {
            $fields = $tableName . ".*";
        }
        $fields = self::rearrangeFields($fields);
        $builder->select($fields);
        return $builder;
    }

    /**
     * @param $tableClassName
     * @param $data
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function insert($tableClassName, $data) {
        $tableName = $tableClassName::getTableName();
        $pdoString = $tableClassName::getPdoString();
        $builder = self::createQueryBuilderFromPdoString($pdoString);
        $builder->insert($tableName);
        foreach ($data as $key => $val) {
            $tableizedKey = Inflector::tableize($key);
            $builder->setValue($tableizedKey, $builder->createNamedParameter($val));
        }
        return $builder;
    }

    /**
     * @param $tableClassName
     * @param array $data
     * @param array $where
     * @return \Doctrine\DBAL\Query\QueryBuilder
     * @throws \Exception
     */
    static function update($tableClassName, array $data, array $where=[]) {
        if (!count($data)) {
            throw new \Exception("CreateBuilder::update requires data to atleast have one field.");
        }
        $tableName = $tableClassName::getTableName();
        $pdoString = $tableClassName::getPdoString();
        $builder = self::createQueryBuilderFromPdoString($pdoString);
        $builder->update($tableName);
        foreach ($data as $key => $val) {
            $builder->set($key, $builder->createNamedParameter($val));
        }
        $first=true;
        foreach ($where as $key => $val) {
            $predicate = $key . "=" . $builder->createNamedParameter($val);
            if($first){
                $builder->where($predicate);
            } else {
                $builder->andWhere($predicate);
            }
            $first=false;
        }
        return $builder;
    }
}