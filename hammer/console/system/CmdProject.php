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
                echo "{$timeout}\n";
                $taskFiles = array_slice(scandir($taskDir), 2);
                var_dump($taskFiles);
                foreach ($taskFiles as $file) {
                    list($time, $project, $branch) = explode('_', $file);
                    if ((time() - $time) > 300) {
                        $cmd = "rm -f {$taskDir}/{$file}";
                        echo "{$cmd}\n";
                        exec($cmd);
                    } else {
                        $logFile = "{$logDir}/{$file}.log";
                        $cmd     = "sh /data/code/debug/code.sh {$project} {$branch} '/hammer' > $logFile";
                        echo "{$cmd}\n";
                        file_put_contents($logFile, "<<<<<<<GIT OK>>>>>>", FILE_APPEND);
                    }
                }

                $logFiles = array_slice(scandir($logDir), 2);
                var_dump($logFiles);
                foreach ($logFiles as $file) {
                    $time = explode('_', $file)[0];
                    if ((time() - $time) > 300) {
                        $cmd = "rm -f {$logDir}/{$file}";
                        echo "{$cmd}\n";
                        exec($cmd);
                    } else {
                        $logOkFile = $file . 'ok';
                        if (in_array($logOkFile, $logFiles)) {
                            $cmd = "rm -f {$logDir}/{$file}";
                            echo "{$cmd}\n";
                            exec($cmd);
                            $cmd = "rm -f {$logDir}/{$logOkFile}";
                            echo "{$cmd}\n";
                            exec($cmd);
                        } else {
                            echo "---- {$logDir}/{$file}\n";
                        }
                    }
                }
                usleep(5000);
                $timeout--;
            }

        }
    }