<?php

namespace modules\dev\v1\api\devtool\runner\args\keywords;

use models\common\ActionBase;
use models\common\sys\Sys;
use modules\dev\v1\dao\devtool\DebugKwDao;


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
        $dao=new DebugKwDao();
        $dao->domain_prefix=$this->getParams()->getStringNotNull('prefix');
        $dao->ver=$this->getParams()->getInt('version');
        $dao->api_name=$this->getParams()->getStringNotNull('api_name');
        $dao->args_json=$this->getParams()->getStringNotNull('args_json');
        $dao->title=$this->getParams()->getStringNotNull('title');
        $dao->brief=$this->getParams()->getStringNotNull('brief');
       // id, domain_prefix, ver, api_name, args_json, title, brief, isdel, create_at, update_at

        $dao->save();
        return $dao->getOpenInfo();


    }

}