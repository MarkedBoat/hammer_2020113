<?php

namespace modules\dev\v1\api\devtool\runner\params;

use models\common\ActionBase;
use modules\dev\v1\dao\devtool\RunnerApi;
use modules\dev\v1\dao\devtool\RunnerApiHis;


class ActionApis extends ActionBase
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
        $data = [];
        $daos = RunnerApi::model()->findAll();
        $rows = [];
        foreach ($daos as $dao)
        {
            $rows[] = $dao->getOpenInfo();
        }
        return $rows;

    }

}
