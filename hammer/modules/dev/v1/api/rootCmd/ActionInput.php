<?php

namespace modules\dev\v1\api\rootCmd;

use models\common\ActionBase;
use models\common\sys\Sys;


class ActionInput extends ActionBase
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
        $cmds = [
            'mysql_env_slave' => 'cat /data/code/debug/poseidon.conf_mysql.slave.php > /data/code/debug/poseidon.conf_mysql',
            'mysql_env_dev'   => 'cat /data/code/debug/poseidon.conf_mysql.dev.php > /data/code/debug/poseidon.conf_mysql',
        ];

        $cmd  = $this->params->getStringNotNull('cmd');
        $date = $this->params->getStringNotNull('date');
        if (!isset($cmds[$cmd]))
            $this->setMsg('cmd error')->setCode('cmd_error')->outError();
        Sys::app()->redis()->lPush(Sys::app()->params['console']['root_cmd_queue'], json_encode([$date, $cmds[$cmd]]));
        return Sys::app()->redis()->lGetRange(Sys::app()->params['console']['root_cmd_queue'], 0, 200);

    }

}