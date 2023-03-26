<?php

namespace modules\dev\v1\api\devtool\runner\params;

use models\common\ActionBase;
use modules\dev\v1\dao\devtool\RunnerApiHis;


class ActionHis extends ActionBase
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
        if ($this->getParams()->tryGetString('prefix'))
        {
            $data['domain_prefix'] = $this->getParams()->tryGetString('prefix');
        }
        if ($this->getParams()->tryGetString('api_name'))
        {
            $data['api_name'] = $this->getParams()->tryGetString('api_name');
        }
        if ($this->getParams()->tryGetInt('ver'))
        {
            $data['ver'] = $this->getParams()->tryGetInt('ver');
        }
        $order_by=$this->getParams()->getStringNotNull('order_by');
        $daos = RunnerApiHis::model()->order(" order by {$order_by} desc ")->findAllByAttributes($data);
        $rows = [];
        foreach ($daos as $dao)
        {
            $rows[] = $dao->getOpenInfo();
        }
        return $rows;

    }

}