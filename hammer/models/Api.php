<?php

    namespace models;

    use models\common\ActionBase;
    use models\common\sys\Sys;


    class Api {

        /**
         * @var ActionBase
         */
        private       $__action  = null;
        public static $hasOutput = false;

        /**
         * Displays homepage.
         *
         * @return string
         */
        public function run() {

            $uri             = trim(preg_replace('/\?(.*)?$/', '', $_SERVER['REQUEST_URI']), '/');
            $arr             = explode('/', $uri);
            $version         = $arr[1];
            $arr[1]          = explode('.', $version)[0];
            $arr[1]          .= '\\api';
            $lastIndex       = count($arr) - 1;
            $action          = $arr[$lastIndex];
            $arr[$lastIndex] = 'Action' . ucfirst($action);
            array_unshift($arr, 'modules');
            $actionClassPath = join('\\', $arr);
            // var_dump($actionClassPath);die;
            Sys::app()->setDebug(isset($_REQUEST['kldebug']) && $_REQUEST['kldebug'] === 'x');
            Sys::app()->cache = isset($_REQUEST['nocache']) ? false : true;
            try {
                // if ($_SERVER['REQUEST_METHOD'] != "POST")
                //     Sys::app()->interruption()->setMsg('请使用post方法')->outError();

                $this->initAction($actionClassPath);
                $this->__action->version = floatval(substr($version,1));
                $this->output();
            } catch (\Exception $e) {
                if (false) {
                    echo "\n trace:\n";
                    echo $e->getMessage();
                    debug_print_backtrace();
                    echo "\n";
                    die;
                } else {
                    $this->outError($e);
                }
            }
            die;

        }


        public function initAction($actionClassPath) {
            if (class_exists($actionClassPath)) {
                $this->__action = new $actionClassPath($_REQUEST);
            } else {
                Sys::app()->interruption()->setMsg('method不存在' . $actionClassPath)->outError();
            }
        }


        public function output() {
            try {
                $data = $this->__action->run();
                ob_end_clean();
                @header('content-Type:application/json;charset=utf8');
                $data = [
                    'status' => 200,
                    'data'   => $data,
                    'code'   => Sys::app()->interruption()->getCode(),
                    // '__ENV__' => __HOST__
                    //   'server'     => ['nowTimestamp' => time()],
                ];
                if ($this->__action->isDebug()) {
                    $data['__debugs'] = [
                        'out'   => __CLASS__ . '==>' . __METHOD__ . '() ##' . __LINE__,
                        'log'   => Sys::app()->interruption()->getLogs(),
                        'error' => error_get_last()
                    ];
                }
                $json = json_encode($data, JSON_UNESCAPED_SLASHES);
                // if ($lastError)
                //    self::lastError('', '', $lastError);
                self::$hasOutput = true;
                die($json);

            } catch (\Exception $exception) {
                $this->outError($exception);
            }
        }

        public function outError(\Exception $exception) {
            ob_end_clean();
            //      @header('HTTP/1.1 200 Not Found');
            //      @header("status: 200 Not Found");
            if (Sys::app()->params['errorHttpCode'] === 400) {
                @header('HTTP/1.1 400 Not Found');
                @header("status: 400 Not Found");
            }

            @header('content-Type:application/json;charset=utf8');
            $data      = [
                'status' => 400,
                'msg'    => $exception->getMessage(),
                'code'   => Sys::app()->interruption()->getCode()
                //'server'     => ['nowTimestamp' => time()],
            ];
            $lastError = error_get_last();

            if (is_null($this->__action) ? Sys::app()->isDebug() : $this->__action->isDebug()) {
                //$data['ENV']       = ENV_NAME;
                // $data['__arg']     = Manger::$isDebug ? Manger::$requstArgs : [];
                $data['__debugs'] = [
                    'out'   => __CLASS__ . '==>' . __METHOD__ . '() ##' . __LINE__,
                    'file'  => $exception->getFile() . '#' . $exception->getLine(),
                    'msg'   => Sys::app()->interruption()->getDebugMsg(),
                    'data'  => Sys::app()->interruption()->getDebugData(),
                    'log'   => Sys::app()->interruption()->getLogs(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                    'error' => $lastError
                ];
            }
            $json = json_encode($data, JSON_UNESCAPED_SLASHES);
            if ($lastError)
                self::lastError('', '', $lastError);
            self::$hasOutput = true;
            die($json);
        }


        public static function lastError($msg, $code, $lastError) {
            $keys = ['Allowed memory size', 'Invalid UTF-8 sequence in argument'];
            $log  = false;
            foreach ($keys as $kw)
                if (strstr($lastError['message'], $kw)) {
                    $log = true;
                    break;
                }
            if ($log) {
                try {/*
                    Sys::redis('mpr')->lPush('mprErrorLog', date('Y-m-d H:i:s') . '###' . json_encode([
                            'isDebuging' => ITFC_DEBUG,
                            'status'     => 400,
                            'msg'        => $msg,
                            'file'       => $lastError['file'] . $lastError['line'],
                            'code'       => $code,
                            'debugMsg'   => $lastError['message'],
                            '__arg'      => Manger::$requstArgs,
                            '__debugs'   => Manger::getDebugInfos(),
                        ]));*/
                    $data['__debugs'][] = ['title' => '记录错误', 'data' => 'ok'];
                } catch (\Exception $e) {
                    if (isset($data['__debugs']))
                        $data['__debugs'][] = ['title' => '记录错误失败', 'data' => $e->getMessage()];
                }
            }

        }
    }