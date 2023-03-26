<?php

namespace modules\dev\v1\api\funtv\conf\file;

use models\common\ActionBase;


class ActionCover extends ActionBase
{
    public static function getClassName()
    {
        return __CLASS__;
    }

    public function __construct($param = [])
    {
        parent::init($param);
    }

    //find /data/code/poseidon_server/ -name mysql_conf.php > /home/kinglone/poseidon_server_mysql_conf_files
    // find poseidon_server  -name index.php |xargs -I {} cp --path {} /data/code/conf/debug/  复制并创建不存在的目录
    public function run()
    {
        $file   = $this->params->getStringNotNull('file');
        $action = $this->params->getStringNotNull('action');
        $file   = '/data/code/' . $file;
        $files  = [
            'to'  => $file,
            'src' => str_replace('.php', ".{$action}.php", $file),
        ];
        foreach ($files as $type => $file)
        {
            if (!is_file($file))
            {
                $this->setMsg($file . '文件不存在')->outError();
            }
            if ($type === 'to' && !is_writeable($file))
            {
                $this->setMsg($file . '文件不可写')->setDebugData($_SERVER)->outError();
            }
        }
        return array('files' => $files, 'action' => $action, 'r' => file_put_contents($files['to'], file_get_contents($files['src'])));
    }

}