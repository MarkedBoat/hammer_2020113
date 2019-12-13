<?php


    //use \models;
    //use \models\Console;

    /*
     * Gogal config
     */
    error_reporting(11);
    defined('__INDEX_DIR__') or define('__INDEX_DIR__', __DIR__);

    /*
     * regist autoloader
     */
    spl_autoload_register(function ($class) {
        if ($class) {
            $file = str_replace('\\', '/', $class);
            $file .= '.php';

            if (file_exists($file)) {
                include $file;
            } else {
                $file = __DIR__ . '/' . $file;
                if (file_exists($file)) {
                    include $file;
                }
            }
        }
    });
    /*
     * process manger
     */
    $console = new \models\Console($argv);
