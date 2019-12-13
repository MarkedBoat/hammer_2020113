<?php


    namespace console\user;

    use models\common\CmdBase;
    use models\common\sys\Sys;

    ini_set('memory_limit', '1024M');

    class CmdExport extends CmdBase {


        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }

        public function hasVipId() {
            $phpFile = Sys::app()->params['console']['logDir'] . '/vip_user.csv';
            $goon    = true;
            $i       = 0;
            $lastId  = 0;
            file_put_contents($phpFile, '');
            while ($goon) {
                $table = Sys::app()->db('cli_bftv_slave')->setText("SELECT id,vipouttime FROM std_user WHERE vipouttime>0 and id>'{$lastId}' ORDER BY id ASC LIMIT 1000")->queryAll();
                if (is_array($table)) {
                    if (count($table) < 1000)
                        $goon = false;
                    foreach ($table as $row) {
                        $str = "{$i},{$row['id']},{$row['vipouttime']}\n";
                        file_put_contents($phpFile, $str, FILE_APPEND);
                        $lastId = $row['id'];
                        $i++;
                    }
                    echo date('Y-m-d H:i:s', time()) . '#' . $str;
                } else {
                    $goon = false;
                }

            }
            echo date('Y-m-d H:i:s', time());
            echo "ok\n";
        }

        public function onlined_uuid() {
            $file     = Sys::app()->params['console']['logDir'] . '/vip_user.csv';
            $file2    = Sys::app()->params['console']['logDir'] . '/vip_uuid.csv';
            $f        = fopen($file, 'r');
            $fileLine = 0;
            $uidsVip  = [];
            $nowTs    = time();
            while (!feof($f)) {
                $fileLine++;
                $str = trim(fgets($f));
                echo $str . "\n";
                if (strlen($str) > 10) {
                    list($i, $uid, $expires) = explode(',', $str);
                    if ($nowTs < intval($expires))
                        $uidsVip[] = $uid;
                }
            }
            fclose($f);
            var_dump($uidsVip);
            $uuids = [];


        }
    }