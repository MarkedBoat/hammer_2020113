<?php

    namespace modules\eg\v1\api\project;

    use models\common\ActionBase;


    class ActionDeploy extends ActionBase {
        public static function getClassName() {
            return __CLASS__;
        }

        public function __construct($param = []) {
            parent::init($param);
        }

        public function run() {
            $project = $this->params->getStringNotNull('project');
            $branch  = $this->params->getStringNotNull('branch');
            //return [$project, $branch];
            $r = exec("sh /data/code/debug/code.sh {$project} {$branch} '/hammer'", $ar);
            return [$ar, $r];

        }

    }