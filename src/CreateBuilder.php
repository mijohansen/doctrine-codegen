<?php

namespace DbUtil;

class CreateBuilder {

    /**
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function createQueryBuilderFromPdoString($pdoFunctionString) {
        $connection = Utils::getConnectionBasedOnPdoFunction($pdoFunctionString);
        return $connection->createQueryBuilder();
    }

    /**
     * @param $tableClassName
     * @param $pdoString
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function select($tableClassName) {
        $tableName = $tableClassName::getTableName();
        $pdoString = $tableClassName::getPdoString();
        $builder = self::createQueryBuilderFromPdoString($pdoString);
        $builder->from($tableName, $tableName);
        $builder->select($tableName . ".*");
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
            $builder->setValue($key, $builder->createNamedParameter($val));
        }
        return $builder;
    }

    /**
     * @param $tableClassName
     * @param $data
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    static function update($tableClassName, $data) {
        $tableName = $tableClassName::getTableName();
        $pdoString = $tableClassName::getPdoString();
        $builder = self::createQueryBuilderFromPdoString($pdoString);
        $builder->insert($tableName);
        foreach ($data as $key => $val) {
            $builder->set($key, $builder->createNamedParameter($val));
        }
        return $builder;
    }
}