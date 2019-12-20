<?php

    namespace modules\eg\v1\api\project;

    use models\common\ActionBase;
    use models\common\sys\Sys;


    class ActionDeploy extends ActionBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);
        }

        public function run() {
            $project = $this->params->getStringNotNull('project');
            $branch  = $this->params->getStringNotNull('branch');

            $time     = time();
            $fileName = "{$time}_{$project}_{$branch}";
            //return [$project, $branch];
            $taskFile = Sys::app()->params['console']['logDir'] . '/project/task/' . $fileName;
            $logFile  = Sys::app()->params['console']['logDir'] . '/project/log/' . $fileName . '.log';

            file_put_contents($taskFile, '');
            $str = '';
            for ($i = 0; $i < 300; $i++) {
                if (file_exists($logFile)) {
                    $str = file_get_contents($logFile);
                    if (strstr($str, '<<<<<<<GIT OK>>>>>>')) {
                        file_put_contents($logFile . 'ok', '');
                        break;
                    }
                };
            }
            die("<pre>{$str}</pre>");
            $r = exec("sh /data/code/debug/code.sh {$project} {$branch} '/hammer'", $ar);
            return [$ar, $r];

        }

    }