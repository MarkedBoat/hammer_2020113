<?php

    /*
     * Gogal config
     */
    error_reporting(11);
    defined('__INDEX_DIR__') or define('__INDEX_DIR__', __DIR__);

    require 'autoloader.php';

    /*
     * process manger
     */
    $console = new \models\Console($argv);

