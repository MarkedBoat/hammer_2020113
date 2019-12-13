<?php


    namespace console\mysql;

    use models\common\CmdBase;
    use models\common\sys\Sys;


    class CmdTmp extends CmdBase {

        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }

        /**
         * 导出连过网的
         */
        public function slave_to_funtv() {

            $dbBf       = Sys::app()->db('cli_bftv_slave');
            $dbFuntv    = Sys::app()->db('funtv');
            $tnsAllData = [
                'std_album',
                'std_episode_qiyi',
                'bftv_copyrights_request_statis',
                'std_album_keywords',
                'std_l_channel_video',
                'bftv_order',
                'std_uuid_getuiid',
                'std_tv_short_video_baofeng',
                'bftv_copyright_request'
            ];
            $tnsSkip    = ['std_user_3rd', 'std_voice_pcklist'];
            $tableTns   = $dbBf->setText("SELECT sum(DATA_LENGTH+INDEX_LENGTH)/1024/1024 'size',`table_name` AS tn FROM INFORMATION_SCHEMA. tables WHERE table_schema = 'baofeng_tv'GROUP BY `table_name`;")->queryAll();

            foreach ($tableTns as $i => $row) {
                $tn = $row['tn'];
                echo "{$i}/{$tn}\n";
                if (in_array($tn, $tnsSkip, true))
                    continue;
                if ($this->getStatus($tn, 'create') === false) {
                    echo "not created\n";
                    $row2 = $dbBf->setText("show create table {$tn};")->queryRow();
                    if (isset($row2['View']))
                        continue;
                    $sql = str_replace(['USING BTREE', 'utf8mb4_unicode_ci', 'utf8mb4'], [
                        '',
                        'utf8_general_ci',
                        'utf8'
                    ], $row2['Create Table']);

                    $ars = explode("\n", $sql);
                    foreach ($ars as $index => $ele) {
                        $ele  = trim($ele);
                        $last = substr($ele, -1);
                        if (strstr($ele, 'KEY') && (strstr($ele, '`,`') || strstr($ele, '(`'))) {
                            if (strstr($ele, 'COMMENT')) {
                                $ars[$index] = explode('COMMENT', $ele)[0] . ($last === ',' ? ',' : '');
                            }
                        }
                    }
                    $sql = join("\n", $ars);
                    $dbFuntv->setText($sql)->execute();
                    $this->logStatus($tn, 'create', 'ok');
                } else {
                    echo "has created\n";
                }

                $limit = 0;
                if (!in_array($tn, $tnsAllData, true) && intval($row['size']) > 500) {
                    $limit = 10000;
                }
                $this->logStatus($tn, 'rowsLimit', $limit);
                if ($this->getStatus($tn, 'coypData') === false) {
                    echo "not coypData\n";

                    $tableInfo = $dbBf->setText("show full columns from {$tn};")->queryRow();
                    $isPkInt   = false;
                    $pk        = '';
                    $fields    = [];
                    $fields2   = [];
                    foreach ($tableInfo as $row2) {
                        if ($isPkInt === false && $row2['Key'] === 'PRI' && strstr($row2['Type'], 'int(')) {
                            $pk      = $row2['Field'];
                            $isPkInt = true;
                        }
                        $fields[]  = "`{$row2['Field']}`";
                        $fields2[] = $row2['Field'];
                    }
                    if ($isPkInt === false) {
                        $this->logStatus($tn, 'coypData', 'noIntPk');
                        continue;
                    }

                    $maxPkVal = $this->getStatus($tn, 'maxPkVal');
                    if ($maxPkVal === false) {
                        $maxPkVal = $dbBf->setText("select max(`{$pk}`) from {$tn}")->queryScalar();
                        $this->logStatus($tn, 'maxId', $maxPkVal);
                    }


                   // $dbFuntv->setText($sql)->execute();
                   // $this->logStatus($tn, 'coypData', 'ok');
                } else {
                    echo "has coypData\n";
                }

            }
        }


        public function logStatus($tn, $key, $status) {
            $tnFile = "/var/log/porter/mysql/status/{$tn}.txt";
            $val    = $this->getStatus($tn, $key);

            if ($val === false) {
                file_put_contents($tnFile, "\n<{$key}:$status", FILE_APPEND);
            } else {
                file_put_contents($tnFile, str_replace("<{$key}:{$val}", "<{$key}:$status", file_get_contents($tnFile)));
            }
        }

        public function getStatus($tn, $key) {
            $tnFile = "/var/log/porter/mysql/status/{$tn}.txt";
            if (!file_exists($tnFile))
                file_put_contents($tnFile, '');
            $str = strstr(file_get_contents($tnFile), "<{$key}:");
            if ($str) {
                return str_replace("<{$key}:", '', explode("\n", $str)[0]);
            } else {
                return false;
            }
        }
    }