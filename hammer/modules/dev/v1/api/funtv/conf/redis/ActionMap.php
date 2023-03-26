<?php

namespace modules\dev\v1\api\funtv\conf\redis;

use models\common\ActionBase;


class ActionMap extends ActionBase
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

        return json_decode(file_get_contents('/data/code/poseidon_server/phplib/common/doc-kl/conf/redis_map.json'),true);
    }

}