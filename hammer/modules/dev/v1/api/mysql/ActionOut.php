<?php

namespace modules\dev\v1\api\mysql;

use models\common\ActionBase;
use models\common\sys\Sys;


class ActionOut extends ActionBase
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

        $table = $this->params->getStringNotNull('table');
        $db    = $this->params->getStringNotNull('db');
        // $table = 'sl_client';
        $sql   = "show full columns from {$table};";
        $table = Sys::app()->db($db)->setText($sql)->queryAll();

        echo '<pre>';
        $attrs = [];
        foreach ($table as $row)
        {
            $type = strstr($row['Type'], 'int') ? 'int' : 'string';
            echo "* @property {$type} {$row['Field']} {$row['Comment']}\n";
            $attrs[] = "'{$row['Field']}'";
        }
        echo "\n";
        $str  = join(',', $attrs);
        $str2 = str_replace("'", '`', $str);
        echo " const fields = '{$str2}';\n\n";
        foreach ($table as $row)
        {
            $type = strstr($row['Type'], 'int') ? 'int' : 'string';
            echo "* @property {$type} _{$row['Field']} {$row['Comment']}\n";
        }
        echo "\n\n\n";
        foreach ($table as $row)
        {
            echo " public \${$row['Field']}; //{$row['Comment']}\n";
        }
        echo " protected \$allAttrKeys = [{$str}];\n";

        die('</pre>');

    }

}