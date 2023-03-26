<?php

namespace modules\dev\v1\api\funtv\conf\redis;

use models\common\ActionBase;


class ActionOpts extends ActionBase
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
        $rows = json_decode(file_get_contents('/data/code/poseidon_server/phplib/common/doc-kl/conf/redis_map_opt.json'), true);
        $map=array();

        foreach ($rows as $row){
            $map[$row['key']]=$row;
        }
        ksort($map);

        return array_values($map);
    }

}