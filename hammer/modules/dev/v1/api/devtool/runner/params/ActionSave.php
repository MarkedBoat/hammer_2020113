<?php

namespace modules\dev\v1\api\devtool\runner\params;

use models\common\ActionBase;
use modules\dev\v1\dao\devtool\RunnerApi;
use modules\dev\v1\dao\devtool\RunnerParam;


class ActionSave extends ActionBase
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
        $dao                = new RunnerApi();
        $args_json          = $this->getParams()->getStringNotNull('params_json');
        $dao->domain_prefix = $this->getParams()->getStringNotNull('prefix');
        $dao->ver           = $this->getParams()->getInt('version');
        $dao->api_name      = $this->getParams()->getStringNotNull('api_name');
        //{"k":"cp","v":"f6y7rpj","fun":"","brief":"","select":true,"vals":[]},
        $param_list = json_decode($args_json, true);
        if (is_array($param_list) && count($param_list))
        {
            $keys = ['k', 'v', 'fun', 'brief'];
            foreach ($param_list as $param_info)
            {
                foreach ($keys as $key)
                {
                    if (!isset($param_info[$key]))
                    {
                        $this->setMsg('丢失' . $key)->outError();
                    }
                }
                $param            = new RunnerParam();
                $param->param_key = $param_info['k'];
                $param->param_val = $param_info['v'];
                $param->fun       = $param_info['fun'];
                $param->brief     = $param_info['brief'];
                $param->param_md5 = md5($param_info['v']);
                $param->trySave();
            }
        }

        $dao->params_json = $args_json;
        $dao->title       = $this->getParams()->getStringNotNull('title');
        $dao->brief       = $this->getParams()->getStringNotNull('brief');
        $dao->setOnDuplicateSet(['params_json' => $args_json]);
        $dao->trySave();
        return $dao->getOpenInfo();

    }

}