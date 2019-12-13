<?php

    /**
     * Created by PhpStorm.
     * User: markedboat
     * Date: 2018/7/20
     * Time: 11:01
     */

    namespace console\tmp;
    ini_set('memory_limit', '4024M');

    use models\common\CmdBase;
    use models\common\sys\Sys;
    use modules\sl\v1\dao\ClientRuleDao;
    use modules\sl\v1\dao\OperSettingDao;
    use modules\sl\v1\dao\user\UserMemberDao;
    use modules\sl\v1\model\Client;
    use modules\sl\v1\model\User;
    use modules\sl\v1\model\user\UserMember;
    use modules\sl\v1\model\wechat\applet\ApiRequest;

    //更新计划凭借代码方法 sh ~/kinglone.sh "/data/git-webroot/api-htdocs/CLI/" "origin/master"
    // 简易计划任务操作  sudo chmod +x /data/git-webroot/api-htdocs/CLI/itfc/hammer cmd:/data/git-webroot/api-htdocs/CLI/itfc/hammer bftv.user.service.renew starter --service=kids --planId=renewKidsMember --env=prod --deadLineTs=1550222461
    class CmdClient extends CmdBase {


        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }


        public function clearClientExtCache() {
            $table = Sys::app()->db('sl_slave')->setText("SELECT id FROM sl_client ;")->queryAll();
            $cnt   = count($table);
            $reids = Sys::app()->redis('default');
            foreach ($table as $i => $row) {
                echo "{$i}/{$cnt} #{$row['id']}#";
                echo $reids->get('sl_c_12_' . $row['id']);
                $reids->del('sl_c_12_' . $row['id']);
                echo "\n";
            }
        }

        public function clearPreClientCache() {
            $table = Sys::app()->db('sl_slave')->setText("SELECT id FROM sl_client WHERE left(str_id,4)='pre_';")->queryAll();
            $cnt   = count($table);
            $reids = Sys::app()->redis('default');
            foreach ($table as $i => $row) {
                echo "{$i}/{$cnt} \n";
                $reids->del('sl_c_13_' . $row['id']);
            }
        }

        public function getPreClientCache() {
            $table = Sys::app()->db('sl_slave')->setText("SELECT id,str_id FROM sl_client WHERE left(str_id,4)='pre_';")->queryAll();
            $cnt   = count($table);
            $reids = Sys::app()->redis('default');
            foreach ($table as $i => $row) {
                echo "{$i}/{$cnt} {$row['str_id']}#";
                echo $reids->get('sl_c_13_' . $row['id']);
                echo "\n";
            }
        }

        public function expired() {
            echo "\n fuck\n";

            $tn        = UserMemberDao::tableName;
            $ets       = time();
            $sts       = $ets - 24 * 3600;
            $cid_names = UserMember::getCidNames();
            $cids      = UserMember::getCids();
            foreach ($cids as $field => $cid) {
                echo " {$field} {$cid_names[$cid]}\n";
                $table = Sys::app()->db('sl_slave')->setText("select u.id,u.open_id,{$field} from {$tn} as um  left join sl_oper as u on um.id=u.id where {$field}<{$ets} and {$field}>={$sts}")->queryAll();
                $cnt   = count($table);
                foreach ($table as $i => $row) {
                    echo " {$field} {$cid_names[$cid]}\t";
                    echo "{$i}/{$cnt} userPk:{$row['id']} {$row['open_id']}\t";
                    // $fromId = false;
                    $fromId = User::getFromId($row['id']);
                    if ($fromId === false) {
                        echo "没有fromId";
                    } else {
                        $date   = date('Y-m-d H:i:s', $row[$field]);
                        $result = ApiRequest::sendMessage(ApiRequest::getUserVipExpired($row['open_id'], $fromId, $cid_names[$cid], $date));
                        // Sys::app()->logInfo([date('Y-m-d H:i:s', $expires), $result], 'wc' . $user->id);
                    }
                    echo "\n";
                }
                echo "\n";
            }
            var_export(Sys::app()->interruption()->getLogs());


        }

        public function fixBindInfo() {
            $table       = Sys::app()->db('sl_slave')->setText("SELECT id,oper FROM sl_client WHERE oper>0;")->queryAll();
            $cnt         = count($table);
            $i           = 1;
            $nowTs       = time();
            $nowDate     = date('Y-m-d H:i:s', $nowTs);
            $sqls1       = [];
            $sqls2       = [];
            $clientPks   = [];
            $userPks     = [];
            $userExtTn   = OperSettingDao::tableName;
            $clientExtTn = ClientRuleDao::tableName;
            foreach ($table as $row) {
                echo "{$i}/{$cnt}/sql\n";
                $sqls1[] = "insert ignore into {$userExtTn} set `oper_id`={$row['oper']},attr='bindTime1st',sn=0,val={$nowTs},cdate=:ndate on duplicate key update val={$nowTs},sta=1,udate=:ndate";
                $sqls2[] = "insert ignore into {$clientExtTn} set `cid`={$row['id']},attr='bindTime1st',sn=0,val={$nowTs},cdate=:ndate on duplicate key update val={$nowTs},sta=1,udate=:ndate";
                $i++;
            }
            $sqlsArray = array_chunk(array_merge($sqls1, $sqls2), 100);
            $cnt       = count($sqlsArray);
            $i         = 1;
            foreach ($sqlsArray as $sqls) {
                echo date('Y-m-d H:i:s', time());
                echo "/{$i}/{$cnt}/sqls\n";
                Sys::app()->db('sl_master')->setText(join(';', $sqls))->bindArray([':ndate' => $nowDate])->execute();
                $i++;
            }
            $i = 1;
            foreach ($table as $row) {
                echo "{$i}/{$cnt}/cache\n";
                User::getByPk($row['oper'])->getExt()->get(true);
                Client::getByPk($row['id'])->getExt()->get(true);
                $i++;
            }
        }

        public function clearCache() {
            $table   = Sys::app()->db('sl_slave')->setText("SELECT id FROM sl_client ;")->queryAll();
            $cnt     = count($table);
            $i       = 1;
            $nowTs   = time();
            $nowDate = date('Y-m-d H:i:s', $nowTs);

            foreach ($table as $row) {
                echo "{$i}/{$cnt}/cache\n";
                Client::getByPk($row['id'])->getExt()->get(true);
                $i++;
            }
        }
    }