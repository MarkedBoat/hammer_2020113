<?php

namespace modules\dev\v1\api\devtool\runner\args\keywords;

use models\common\ActionBase;
use models\common\sys\Sys;
use modules\dev\v1\dao\devtool\DebugKwDao;


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
        $dao=DebugKwDao::model()->findByPk($this->getParams()->getIntNotNull('logic_id'));
        if(empty($dao)){
            $this->setMsg('没有id')->outError();
        }
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