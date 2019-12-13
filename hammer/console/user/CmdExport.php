<?php


    namespace console\user;

    use models\common\CmdBase;
    use models\common\sys\Sys;

    date_default_timezone_set('PRC');
    ini_set('date.timezone', 'Asia/Shanghai');

    class CmdExport extends CmdBase {


        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }

        public function hasVipId() {
            date_default_timezone_set('PRC');

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
    }