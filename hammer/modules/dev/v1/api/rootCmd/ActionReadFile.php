<?php

namespace modules\dev\v1\api\rootCmd;

use models\common\ActionBase;


class ActionReadFile extends ActionBase
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
        if (!is_file($file) || !is_writeable($file) || !strstr($file, 'debug'))
            $this->setCode('file_error')->setMsg('file_error')->outError();
        $data = json_decode(file_get_contents($file), true);
        if (!is_array($data))
            $this->setCode('json_error')->setMsg('json错误')->outError();
        return $data;
    }

}