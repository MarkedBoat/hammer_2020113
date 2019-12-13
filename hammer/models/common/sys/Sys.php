<?php

    namespace models\common\sys;

    use models\common\db\MCD;
    use models\common\db\MysqlPdo;
    use models\common\error\Interruption;
    use models\common\param\WebRequest;

    /**
     * Class Sys
     * @package models\common\sys
     *
     * @property WebRequest $webRequest
     */
    class Sys {
        private static $__case      = null;
        private        $__configs   = [];
        private        $__cases     = [];//实例
        private        $__isDebug   = false;
        public         $cache       = true;
        public         $params      = [];
        private        $__propertys = [];
        const cfgKeyRedis = 'redis';

        /**
         * @return Sys
         * @throws \Exception
         */
        public static function app() {
            if (is_null(self::$__case))
                throw  new \Exception('Sys 并未init~!');
            return self::$__case;
        }

        public static function init($configs) {
            self::$__case            = new Sys();
            self::$__case->__configs = $configs;
            if (isset($configs['params']))
                self::$__case->params = $configs['params'];
        }


        public function getConfig() {
            return $this->__configs;
        }

        public function setConfig($configs) {
            $this->__configs = $configs;
        }

        public function addConfig($key, $config) {
            $this->__configs[$key] = $config;
        }

        public function addCase($key, $case) {
            $this->__cases[$key] = $case;
        }

        /**
         * @param $redisKey
         * @return \Redis
         * @throws \Exception
         */
        public function redis($redisKey = 'default') {
            if (isset($this->__configs['redis'][$redisKey])) {
                if (!isset($this->__cases['redis']))
                    $this->__cases['redis'] = [];
                if (!isset($this->__cases['redis'][$redisKey])) {
                    try {
                        $this->__cases['redis'][$redisKey] = new \Redis();
                        $this->__cases['redis'][$redisKey]->connect($this->__configs['redis'][$redisKey]['host'], $this->__configs['redis'][$redisKey]['port']);
                        if (isset($this->__configs['redis'][$redisKey]['password']))
                            $this->__cases['redis'][$redisKey]->auth($this->__configs['redis'][$redisKey]['password']);
                        if (isset($this->__configs['redis'][$redisKey]['db']))
                            $this->__cases['redis'][$redisKey]->select($this->__configs['redis'][$redisKey]['db']);

                    } catch (\Exception $exception) {
                        throw  new \Exception($exception->getMessage(), $exception->getCode(), '', $this->__configs['redis'][$redisKey]);
                    }

                }
            } else {
                throw  new \Exception('没有配置redis信息', 400);
            }
            return $this->__cases['redis'][$redisKey];
        }

        /**
         * @return MCD
         * @throws \Exception
         */
        public function memcached() {
            if (isset($this->__cases['memcached']))
                return $this->__cases['memcached'];
            if (isset($this->__configs['memcached'])) {
                if (!isset($this->__cases['memcached'])) {
                    try {
                        $this->__cases['memcached'] = new MCD($this->__configs['memcached']);
                    } catch (\Exception $exception) {
                        throw  new \Exception($exception->getMessage(), $exception->getCode(), '');
                    }
                }
            } else {
                throw  new \Exception('没有配置memcached信息', 400);
            }
            return $this->__cases['memcached'];
        }


        /**
         * @param $dbKey
         * @return MysqlPdo
         * @throws \Exception
         */
        public function db($dbKey) {
            if (isset($this->__configs['db'][$dbKey])) {
                if (!isset($this->__cases['db']))
                    $this->__cases['db'] = [];
                if (!isset($this->__cases['db'][$dbKey])) {
                    $this->__cases['db'][$dbKey] = MysqlPdo::configDb($this->__configs['db'][$dbKey]);
                }
            } else {
                Sys::app()->interruption()->setMsg('没有配置信息:db>' . $dbKey . '>' . ENV_NAME)->outError();
            }
            return $this->__cases['db'][$dbKey];
        }

        public function unsetDb($dbKey) {
            if (isset($this->__configs['db'][$dbKey])) {
                if (!isset($this->__cases['db']))
                    $this->__cases['db'] = [];
                if (isset($this->__cases['db'][$dbKey])) {
                    $this->__cases['db'][$dbKey] = null;
                    return true;
                } else {
                    return false;
                }
            } else {
                Sys::app()->interruption()->setMsg('没有配置信息:db>' . $dbKey . '>' . ENV_NAME)->outError();
            }
        }


        public static function getRemoteIp() {
            return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        }

        public function isDebug() {
            return $this->__isDebug;
        }

        public function setDebug($status) {
            $this->__isDebug = $status;
        }

        /**
         * @return Interruption
         */
        public function interruption() {
            if (!isset($this->__cases['interruption']))
                $this->__cases['interruption'] = new Interruption();
            return $this->__cases['interruption'];
        }

        public function logInfo($data, $title = false, $trace = true,$deep=0) {
            if ($this->__isDebug === false)
                return false;
            if ($trace) {
                $step = debug_backtrace()[$deep];
                self::app()->interruption()->logInfo([
                    (($title ? $title : '') . ' ==>' . $step['class'] . '->' . $step['function'] . '() #' . $step['line']),
                    $data,
                ]);
            } else {
                self::app()->interruption()->logInfo([$title, $data]);
            }

        }


        public function __get($name) {
            if (!isset($this->__propertys[$name])) {
                switch ($name) {
                    case 'webRequest':
                        $this->__propertys['webRequest'] = new WebRequest();
                        break;
                }
            }
            return $this->__propertys[$name];
        }

    }

