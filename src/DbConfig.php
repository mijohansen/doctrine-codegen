<?php
/**
 * Created by PhpStorm.
 * User: michael
 * Date: 2019-02-23
 * Time: 12:23
 */

namespace DbUtil;

class DbConfig {

    private $dsn;
    private $username;
    private $passwd;

    /**
     * @return mixed
     */
    public function getDsn() {
        return $this->dsn;
    }

    /**
     * @param mixed $dsn
     */
    public function setDsn($dsn) {
        $this->dsn = $dsn;
    }

    /**
     * @return mixed
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPasswd() {
        return $this->passwd;
    }

    /**
     * @param mixed $passwd
     */
    public function setPasswd($passwd) {
        $this->passwd = $passwd;
    }

}
