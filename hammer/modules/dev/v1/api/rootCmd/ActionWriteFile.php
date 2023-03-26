<?php

namespace modules\dev\v1\api\rootCmd;

use models\common\ActionBase;


class ActionWriteFile extends ActionBase
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public function __construct($param = [])
    {
        parent::init($param);
    }

    public function run()
    {
        $file = $this->params->getStringNotNull('file');
        $json = $this->params->getStringNotNull('json');
        $data = json_decode($json, true);
        if (!is_array($data))
            $this->setCode('json_error')->setMsg('json错误')->outError();
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if (!is_file($file) || !is_writeable($file) || !strstr($file, 'debug'))
            $this->setCode('file_error')->setMsg('file_error')->outError();
        return [file_put_contents($file, $json),file_get_contents($file, $json)];
    }

}