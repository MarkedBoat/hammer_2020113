<?php

namespace modules\dev\v1\api\devtool\runner\args\keywords;

use models\common\ActionBase;
use modules\dev\v1\dao\devtool\DebugKwDao;


class ActionList extends ActionBase
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
        $daos = DebugKwDao::model()->findAllByAttributes(['domain_prefix' => $this->getParams()->getStringNotNull('prefix'), 'api_name' => $this->getParams()->getStringNotNull('api_name')]);
        $rows = [];
        foreach ($daos as $dao)
        {
            $rows[] = $dao->getOpenInfo();
        }
        return $rows;

    }

}