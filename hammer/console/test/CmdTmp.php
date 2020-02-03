<?php


    namespace console\test;

    use models\common\CmdBase;
    use models\common\sys\Sys;


    class CmdTmp extends CmdBase {

        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }

        /**
         * 导出连过网的
         */
        public function test() {
            echo "\nok\n";
        }
    }