<?php

namespace modules\dev\v1\api\funtv\conf\db;

use models\common\ActionBase;


class ActionMap extends ActionBase
{
    const TRY_MAX       = 3;
    const host0         = 'mysql:host=192.168.8.194;port=3307';
    const host1         = 'mysql:host=192.168.8.194;port=3307';
    const host_slave2_0 = 'mysql:host=192.168.8.194;port=3315';
    const host_slave2_1 = 'mysql:host=192.168.8.194;port=3315';
    const user          = 'poseidon_test';
    const passwd        = 'funshion_test';

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

        return json_decode(file_get_contents('/data/code/poseidon_server/phplib/common/doc-kl/conf/mysql_map.json'),true);
    }

}