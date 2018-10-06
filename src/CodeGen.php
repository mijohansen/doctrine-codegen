<?php

namespace DbUtil;

use Doctrine\Common\Inflector\Inflector;
use Doctrine\DBAL\Query\QueryBuilder;
use gossi\codegen\generator\CodeGenerator;
use gossi\codegen\model\PhpClass;
use gossi\codegen\model\PhpConstant;
use gossi\codegen\model\PhpMethod;
use gossi\codegen\model\PhpParameter;
use gossi\docblock\Docblock;
use gossi\docblock\tags\ReturnTag;
use PDO;

class CodeGen {

    static function generate($config) {
        $pdo_function = $config["pdo"];
        $base_namespace = $config["namespace"];
        $base_folder = $config["folder"];
        $db = call_user_func($pdo_function);
        $all_tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $classes = [];
        $all_fields = [];
        foreach ($all_tables AS $table_name) {
            $sub = "SHOW COLUMNS FROM $table_name";
            $columns = $db->query($sub)->fetchAll(PDO::FETCH_COLUMN);
            $class_name = Inflector::classify($table_name);
            $qualified_name = $base_namespace . "\\" . $class_name;
            $class = new PhpClass();
            $class->setQualifiedName($qualified_name);
            foreach ($columns as $column) {
                $constant_name = strtoupper($column);
                $constant = new PhpConstant();
                $constant->setName($constant_name);
                $constant->setValue($table_name . "." . $column);
                $class->setConstant($constant);
                $all_fields[$table_name . "." . $column] = $class;
            }
            self::add_table_name_method($class, $table_name);
            self::add_pdo_string_method($class,$pdo_function);
            self::add_select_builder_method($class);
            self::add_insert_builder_method($class);
            self::add_update_builder_method($class);
            self::add_get_fields_method($class);
            $classes[$class_name] = $class;
        }

        foreach ($all_fields as $from_field => $from_class) {
            foreach ($all_fields as $to_field => $to_class) {
                list($from_table_name, $from_field_name) = explode(".", $from_field);
                list($to_table_name, $to_field_name) = explode(".", $to_field);
                if (
                    $from_table_name != $to_table_name &&
                    $from_field_name === $to_field_name &&
                    !in_array($from_field_name, ["created", "deleted", "updated"])
                ) {
                    $from_class_name = Inflector::classify($from_table_name);
                    $to_class_name = Inflector::classify($to_table_name);
                    self::add_join_method($from_class, "innerJoin" . $to_class_name, "inner", $from_field, $to_field);
                    self::add_join_method($from_class, "leftJoin" . $to_class_name, "left", $from_field, $to_field);
                }
            }
        }

        $generator = new CodeGenerator();
        foreach ($classes as $class_name => $class) {
            $code = $generator->generate($class);
            file_put_contents($base_folder . DIRECTORY_SEPARATOR . "$class_name.php", "<?php\n\n" . $code);
        }
    }

    static function add_table_name_method(PhpClass $class, $tableName) {
        $method = new PhpMethod("getTableName");
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return "' . $tableName . '";
            ');
        $class->setMethod($method);
    }

    static function add_pdo_string_method(PhpClass $class, $pdoString) {
        $method = new PhpMethod("getPdoString");
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return "' . $pdoString . '";
            ');
        $class->setMethod($method);
    }
    static function add_get_fields_method($class) {
        $method = new PhpMethod("getFields");
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return \\' . Utils::class . '::getFields(self::class);
            ');
        $class->setMethod($method);
    }

    static function add_select_builder_method(PhpClass $class) {
        $method = new PhpMethod("select");
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return \\' . CreateBuilder::class . '::select(self::class);
            ');
        $doc = new Docblock();
        $doc->appendTag(new ReturnTag("\\" . QueryBuilder::class));
        $method->setDocblock($doc);
        $class->setMethod($method);
    }

    static function add_insert_builder_method(PhpClass $class) {
        $method = new PhpMethod("insert");
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return \\' . CreateBuilder::class . '::insert(self::class, $data);
            ');
        $doc = new Docblock();
        $doc->appendTag(new ReturnTag("\\" . QueryBuilder::class));
        $method->setDocblock($doc);

        $param = new PhpParameter();
        $param->setName("data");
        $param->setType([]);
        $method->setParameters([$param]);

        $class->setMethod($method);
    }

    static function add_update_builder_method(PhpClass $class) {
        $method = new PhpMethod("update");
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return \\' . CreateBuilder::class . '::update(self::class, $data);
            ');
        $doc = new Docblock();
        $doc->appendTag(new ReturnTag("\\" . QueryBuilder::class));
        $method->setDocblock($doc);
        $param = new PhpParameter();
        $param->setName("data");
        $param->setType([]);
        $method->setParameters([$param]);
        $class->setMethod($method);
    }

    static function add_join_method(PhpClass $class, $method_name, $func, $from_field, $to_field) {
        $method = new PhpMethod($method_name);
        $method->setStatic(true);
        $method->setVisibility(PhpMethod::VISIBILITY_PUBLIC);
        $method->setBody('
            return \\' . Join::class . '::' . $func . '($builder, "' . $from_field . '", "' . $to_field . '");
            ');
        $doc = new Docblock();
        $doc->appendTag(new ReturnTag("\\" . QueryBuilder::class));
        $method->setDocblock($doc);
        $param = new PhpParameter();
        $param->setName("builder");
        $param->setType("\\" . QueryBuilder::class);
        $method->setParameters([$param]);
        $class->setMethod($method);
    }

}