<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 2019-02-23
 * Time: 12:31
 */

namespace DbUtil;

class CodeGenConfig {

    protected $namespace;
    protected $pdo;
    protected $targetFolder;
    protected $autoRemovePrefix = true;

    /**
     * @return bool
     */
    public function isAutoRemovePrefix() {
        return $this->autoRemovePrefix;
    }

    /**
     * @param bool $autoRemovePrefix
     */
    public function setAutoRemovePrefix($autoRemovePrefix) {
        $this->autoRemovePrefix = $autoRemovePrefix;
    }

    /**
     * @return mixed
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     */
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getPdo() {
        return $this->pdo;
    }

    /**
     * @param mixed $pdo
     */
    public function setPdo($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * @return mixed
     */
    public function getTargetFolder() {
        return $this->targetFolder;
    }

    /**
     * @param mixed $targetFolder
     */
    public function setTargetFolder($targetFolder) {
        $this->targetFolder = $targetFolder;
    }

}