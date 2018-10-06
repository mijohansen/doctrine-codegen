<?php

namespace DbUtil;
use Doctrine\DBAL\Query\QueryBuilder;

class Join {

    /**
     * @param QueryBuilder $builder
     * @param $from_field
     * @param $to_field
     * @return QueryBuilder
     */
    static function inner(QueryBuilder $builder, $from_field, $to_field) {
        list($from_table_name, $from_field_name) = explode(".", $from_field);
        list($to_table_name, $to_field_name) = explode(".", $to_field);
        $builder->innerJoin($from_table_name, $to_table_name, $to_table_name, $from_field . "=" . $to_field);
        return $builder;
    }

    /**
     * @param QueryBuilder $builder
     * @param $from_field
     * @param $to_field
     * @return QueryBuilder
     */
    static function left(QueryBuilder $builder, $from_field, $to_field) {
        list($from_table_name, $from_field_name) = explode(".", $from_field);
        list($to_table_name, $to_field_name) = explode(".", $to_field);
        $builder->leftJoin($from_table_name, $to_table_name, $to_table_name, $from_field . "=" . $to_field);
        return $builder;
    }
}