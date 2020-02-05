<?php

    namespace modules\dev\v1\api;

    use models\common\ActionBase;
    use models\common\sys\Sys;


    class ActionUpload extends ActionBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);
        }

        public function run() {
            var_dump($_FILES);
        }

    }