<?php

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

    defined('__INDEX_DIR__') or define('__INDEX_DIR__', __DIR__);
    defined('__HOST__') or define('__HOST__', $_SERVER['HTTP_HOST']);

    $host        = __HOST__;
    $configFiles = [
        'kl-screenlock.fengmi.tv'   => 'test',
        'kl1-screenlock.fengmi.tv'  => 'test',
        'huxiaobao.fangtangtv.com'  => 'prod',
        'pad-hxb.fangtangtv.com'    => 'prod',
        'huxiaobao1.fangtangtv.com' => 'pre',
    ];


    function lastError() {
        if (\models\Api::$hasOutput)
            return false;
        $d = error_get_last();
        if ($d) {
            ob_end_clean();
            // if( \models\common\sys\Sys::app()->params['errorHttpCode']===400){
            @header('HTTP/1.1 400 Not Found');
            @header("status: 400 Not Found");
            //}

            @header('content-Type:text/json;charset=utf8');
            $data = ['status' => 400, 'code' => 'code_error_', 'msg' => '服务器错误',];
            if (\models\common\sys\Sys::app()->isDebug()) {
                $d['message']    = explode("\n", $d['message']);
                $data['__debug'] = [
                    'out'   => __CLASS__ . '==>' . __METHOD__ . '() ##' . __LINE__,
                    'log'   => \models\common\sys\Sys::app()->interruption()->getLogs(),
                    'error' => $d
                ];
            }
            echo json_encode($data);
        } else {

        }
    }

    register_shutdown_function('lastError');

    function tmp_autoload($class) {
        if ($class) {
            $file = __INDEX_DIR__ . '/' . str_replace(['\\'], ['/'], $class);
            $file .= '.php';
            if (file_exists($file)) {
                include $file;
            }
        }
    }

    spl_autoload_register('tmp_autoload');
    if (isset($configFiles[$host])) {
        $dir    = __DIR__ . "/config/hosts/{$configFiles[$host]}.php";
        $config = require __DIR__ . "/config/env/{$configFiles[$host]}.php";
        \models\common\sys\Sys::init($config);
    } else {
        die('domain has not configed');
    }

    (new \models\Api())->run();


