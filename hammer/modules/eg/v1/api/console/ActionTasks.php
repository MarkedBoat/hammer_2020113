<?php

    namespace modules\eg\v1\api\console;

    use models\common\ActionBase;
    use models\common\sys\Sys;


    class ActionTasks extends ActionBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);
        }

        public function run() {

            //return [$project, $branch];
            //  $taskFile = Sys::app()->params['console']['logDir'] . '/project/task/' . $fileName;
            // $logFile  = Sys::app()->params['console']['logDir'] . '/project/log/' . $fileName . '.log';
            return Sys::app()->params['console']['tasks'];

        }

    }