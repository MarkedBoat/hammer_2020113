<?php

    namespace modules\eg\v1\api\console;

    use console\system\CmdLauncher;
    use models\common\ActionBase;


    class ActionKill extends ActionBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);
        }

        public function run() {
            $planId = $this->params->getStringNotNull('planId');
            //  return CmdLauncher::getPlanRunning($planId);
            //return [$project, $branch];
            //  $taskFile = Sys::app()->params['console']['logDir'] . '/project/task/' . $fileName;
            // $logFile  = Sys::app()->params['console']['logDir'] . '/project/log/' . $fileName . '.log';
            $ar   = CmdLauncher::getPlanRunning($planId);
            $data = ['kill' => []];
            foreach ($ar as $str) {
                if ($str) {
                    preg_match('/\d+/', $str, $ar2);
                    if (count($ar2)) {
                        $pid = $ar2[0];
                        if ($pid) {
                            exec("kill {$pid}", $ar3);
                            $data['kill'][] = [$str, $pid, $ar3];
                        }else{
                            $data['kill'][] = $str;
                        }

                    }else{
                        $data['kill'][] = $str;
                    }
                }else{
                    $data['kill'][] = $str;
                }

            }
            return $data;

        }

    }