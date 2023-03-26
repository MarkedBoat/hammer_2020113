<?php

namespace modules\dev\v1\api\devtool\runner\params;

use models\common\ActionBase;
use modules\dev\v1\dao\devtool\RunnerApi;
use modules\dev\v1\dao\devtool\RunnerApiHis;
use modules\dev\v1\dao\devtool\RunnerParam;


class ActionAdd extends ActionBase
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
        $his                = new RunnerApiHis();
        $args_json          = $this->getParams()->getStringNotNull('params_json');
        $dao->domain_prefix = $this->getParams()->getStringNotNull('prefix');
        $dao->ver           = $this->getParams()->getInt('version');
        $dao->api_name      = $this->getParams()->getStringNotNull('api_name');

        $his->domain_prefix = $dao->domain_prefix;
        $his->ver           = $dao->ver;
        $his->api_name      = $dao->api_name;

        //{"k":"cp","v":"f6y7rpj","fun":"","brief":"","select":true,"vals":[]},
        $param_list = json_decode($args_json, true);
        $params_    = [];//业务专用参数
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
                $param = new RunnerParam();

                $param->domain_prefix = $dao->domain_prefix;
                $param->ver           = $dao->ver;
                $param->api_name      = $dao->api_name;
                $param->param_key     = $param_info['k'];
                $param->param_val     = $param_info['v'];
                $param->fun           = $param_info['fun'];
                $param->brief         = $param_info['brief'];
                $param->param_md5     = md5($param_info['v']);
                $param->trySave();
                if (substr($param->param_key, 4) === 'adv_' || $param_info['k'] === 'ca')
                {
                    continue;
                }
                $params_[$param->param_key] = $param->param_val;
            }
        }
        ksort($params_);
        $md5              = md5(json_encode($params_));
        $dao->params_json = $args_json;
        $dao->title       = $this->getParams()->getStringNotNull('title');
        $dao->brief       = $this->getParams()->getStringNotNull('brief');

        $his->params_json = $args_json;
        $his->params_md5  = $md5;
        $his->title       = $this->getParams()->getStringNotNull('title');
        $his->brief       = $this->getParams()->getStringNotNull('brief');
        $his->times       = 1;

        $dao->setOnDuplicateSet(['params_json' => $args_json]);
        $dao->trySave();

        $his->setOnDuplicateSet(['times=times+1', 'title' => $his->title, 'brief' => $his->brief]);
        $his->trySave();


        return $dao->getOpenInfo();


    }

}