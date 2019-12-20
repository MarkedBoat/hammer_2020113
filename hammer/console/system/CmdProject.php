<?php

    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2018/7/20
     * Time: 11:01
     */

    namespace console\system;

    use models\common\CmdBase;
    use models\common\sys\Sys;

    class CmdProject extends CmdBase {


        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }


        public function scan() {
            echo "\n fuc\n";
            $taskDir = Sys::app()->params['console']['logDir'] . '/project/task';
            $logDir  = Sys::app()->params['console']['logDir'] . '/project/log';
            foreach ([$taskDir, $logDir] as $dir)
                if (!file_exists($dir)) {
                    //exec("touch {$logFileName}");
                    exec("mkdir -p {$dir}");
                    exec("chmod 777 {$dir}");
                };

            $timeout = $this->params->getIntNotNull('timeLimit');
            while ($timeout > 7000) {
                $files = array_slice(scandir($taskDir), 1);
                var_dump($files);
                sleep(10);
            }

        }
    }