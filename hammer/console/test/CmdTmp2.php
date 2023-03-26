<?php


    namespace console\test;

    use models\common\CmdBase;
    use models\common\sys\Sys;


    /**
     * Class CmdTmp2
     * @package console\test
     */
    class CmdTmp2 extends CmdBase {

        /**
         * @return string
         */
        public static function getClassName() {
            return __CLASS__;
        }


        /**
         *
         */
        public function init() {
            parent::init();
        }


        public function out(){
            $r=$this->params->tryGetInt('r');
            echo "\nddddddddddd\n";
            if($r!==1)throw new \Exception('ddd',11);
            return true;
       }
    }