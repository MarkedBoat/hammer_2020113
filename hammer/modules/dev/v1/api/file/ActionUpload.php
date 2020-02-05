<?php

    namespace modules\dev\v1\api\file;

    use models\common\ActionBase;


    class ActionUpload extends ActionBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);
        }

        public function run() {
            if (isset($_FILES['file']))
                $this->setMsg('没有文件')->outError();;
            $file = $_FILES['file'];
            $dir  = __INDEX_DIR__ . '/upload';
            if (!file_exists($dir))
                mkdir($dir);
            move_uploaded_file($file['tmp_name'], $dir . '/' . $file['name']);
            return ['file' => $file];

        }

    }