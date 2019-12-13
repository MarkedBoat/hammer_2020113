<?php

    namespace models\common;

    use models\common\param\Params;
    use models\common\sys\Sys;
    use modules\sl\v1\model\Security;


    /**
     * Class Action
     * @package models
     * 接口具体方法的抽你类
     */
    abstract class ActionBase {
        /**
         * @var Params
         */
        protected $params         = null;
        public    $uniqueId       = '';
        private   $__errorMessage = '';
        private   $__errorCode    = 400;
        private   $__debugMessage = '';
        private   $__debugData    = null;
        private   $__logResult    = false;
        private   $__debug        = false;
        private   $__apiName      = '';

        protected $timeout        = 0;
        protected $hasSign        = true;
        protected $signVerifyRate = 100;
        public    $version        = 0;


        public function init($param = []) {
            $this->params    = new Params($param);
            $this->__apiName = $this->params->tryGetString('method');
        }

        public function setParams(Params $Params) {
            $this->params = $Params;
            return $this;
        }

        public function initCmd(Params $Params) {
            $this->setParams($Params);
            $this->run();
        }

        public static function getClassName() {
            return __CLASS__;
        }

        public static function getActionName() {
            return static::getClassName();
        }

        /**
         * @return Params
         */
        public function getParams() {
            return $this->params;
        }

        public abstract function run();


        public function setMsg($msg) {
            Sys::app()->interruption()->setMsg($msg);
            $this->__errorMessage = $msg;
            return $this;
        }


        public function setCode($code) {
            $this->__errorCode = $code;
            Sys::app()->interruption()->setCode($code);
            return $this;
        }

        /**
         * @param $debugMessage
         * @return ActionBase
         */
        public function setDebugMsg($debugMessage) {
            Sys::app()->interruption()->setDebugMsg($debugMessage);
            $this->__debugMessage = $debugMessage;
            return $this;
        }

        /**
         * @param $data
         * @return ActionBase
         */
        public function setDebugData($data) {
            Sys::app()->interruption()->setDebugData($data);
            $this->__debugData = $data;
            return $this;
        }

        /**
         * @throws \Exception
         */
        public function outError() {
            Sys::app()->interruption()->outError();
        }

        /**
         * 记录输出结果
         * @param bool $debug 结果中要不要debug？
         */
        public function logResult($debug = false) {
            $this->__logResult = true;
            $this->__debug     = $debug;
        }

        public function isLogResult() {

            return $this->__logResult;
        }

        public function debug() {
            $this->__debug = true;
        }

        public function isDebug() {
            return $this->getParams()->tryGetString('kldebug') == 'x' ? true : false;
            //   return $this->__debug;
        }

        public function logInfo($info, $title = false) {
            Sys::app()->logInfo($info, $title, true, 2);
        }

        public function setApiName($apiName) {
            $this->__apiName = $apiName;
        }

        public function getApiName() {
            return $this->__apiName;
        }

        public function initQueryParams() {
            $query = $this->getParams()->getStringNotNull('query');

            $array = json_decode($query, true);
            if (!is_array($array))
                $this->setMsg('query解析失败')->setCode('json_decode_error')->outError();

            if ($this->hasSign) {
                $sign = $this->getParams()->getStringNotNull('sign');
                if ($sign !== Sys::app()->params['debugSign'])
                    $this->verifyRSASign($query, $sign);
                $array['sign'] = $sign;
                $this->logInfo('验证参数');
            }


            $array['query']   = $query;
            $array['kldebug'] = $this->getParams()->tryGetString('kldebug');
            $this->setParams(new Params($array));

        }

        public function verifyRSASign($query, $sign) {
            $result = Security::verifyRSASign($query, $sign);
            if (Sys::app()->isDebug()) {
                Sys::app()->logInfo(Security::RSASign($query));
            }
            if (!$result)
                Sys::app()->interruption()->setMsg('签名错误')->setCode('sign_error')->outError();
        }

        public function getRSASign($query) {
            $sign = Security::RSASign($query);
            if (!$sign)
                Sys::app()->interruption()->setMsg('签名错误')->setCode('get_sign_error')->outError();
            return $sign;
        }

        public function outJsonQuery() {
            $ar = $_REQUEST;
            foreach (['query', 'sign', 'kldebug', 'userToken'] as $key)
                unset($ar[$key]);
            echo "\n";
            //$query = json_encode($ar, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $query = json_encode($ar, JSON_UNESCAPED_UNICODE);
            $sign  = Security::RSASign($query);
            echo "query:{$query}\nsign:{$sign}";
            echo "\n";
            die;
        }


        public function initQueryArgs() {
            $query = $this->params->getStringNotNull('query');

            $array = json_decode($query, true);
            if (!is_array($array))
                $this->setMsg('query解析失败')->setCode('json_decode_error')->outError();

            if ($this->hasSign) {
                $verify = true;
                if ($this->signVerifyRate < 100)
                    $verify = $this->signVerifyRate > rand(0, 100);
                if ($verify) {
                    $sign = $this->params->getStringNotNull('sign');
                    $this->logInfo('验证签名');
                    $this->verifyRSASign($query, $sign);
                    $array['sign'] = $sign;
                } else {
                    $this->logInfo('不验证签名');
                }

            }


            $array['query']   = $query;
            $array['kldebug'] = $this->params->tryGetString('kldebug');
            $this->setParams(new Params($array));

        }


    }

