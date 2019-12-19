<?php


    namespace console\tv;

    use models\common\CmdBase;
    use models\common\sys\Sys;


    class CmdExport extends CmdBase {

        public static function getClassName() {
            return __CLASS__;
        }


        public function init() {
            parent::init();
        }

        /**
         * 导出连过网的
         */
        public function onlined() {
            die("任务结束，防止误操作");

            $phpFile = Sys::app()->params['console']['logDir'] . '/onlined_uuid.csv';
            $goon    = true;
            $i       = 0;
            $lastId  = 0;
            file_put_contents($phpFile, '');
            while ($goon) {
                $table = Sys::app()->db('cli_bftv_slave')->setText("select  id, uuid, getuiid, platform, sys_version, softid, launcher_version,ip, province, city from std_uuid_getuiid where id>{$lastId} order by id asc limit 1000")->queryAll();
                if (is_array($table)) {
                    if (count($table) < 1000)
                        $goon = false;
                    foreach ($table as $row) {
                        $ar  = array_map(function ($ele) {
                            return str_replace(',', '#', $ele);
                        }, $row);
                        $str = $i . ',' . join(',', $ar) . "\n";
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

        /**
         * 给设备列表打上是否vip
         */
        public function vip_uuid() {
            die("任务结束，防止误操作");
            $fileUuidsVip   = Sys::app()->params['console']['logDir'] . '/vip_uuid.csv';
            $fileUuidsSta   = Sys::app()->params['console']['logDir'] . '/uuid_vip_sta.csv';
            $fileUuidOnline = Sys::app()->params['console']['logDir'] . '/onlined_uuid.csv';
            $f              = fopen($fileUuidsVip, 'r');
            $fileLine       = 0;
            $uuidsVip       = [];
            while (!feof($f)) {
                $fileLine++;
                $str        = trim(fgets($f));
                $uuidsVip[] = $str;
                echo $fileLine . '#' . $str . "\n";
            }
            fclose($f);

            $f = fopen($fileUuidOnline, 'r');
            file_put_contents($fileUuidsSta, 'i,id, uuid, getuiid, platform, sys_version, softid, launcher_version,  ip, province, city,is_vip' . "\n");
            while (!feof($f)) {
                $str   = trim(fgets($f));
                $uuid  = explode(',', $str)[2];
                $isVip = in_array($uuid, $uuidsVip, true) ? '1' : '0';
                $str   = "{$str},{$isVip}\n";
                echo $str;
                file_put_contents($fileUuidsSta, $str, FILE_APPEND);
            }
            fclose($f);

        }


        /**
         * 给设备列表打上是否vip
         */
        public function vip_uuid_only() {
            $fileUuidsSta    = Sys::app()->params['console']['logDir'] . '/uuid_vip_sta.csv';
            $fileUuidVipOnly = Sys::app()->params['console']['logDir'] . '/uuid_vip_only.csv';

            $f = fopen($fileUuidsSta, 'r');
            file_put_contents($fileUuidVipOnly, '');
            $i = 1;
            while (!feof($f)) {
                $str = trim(fgets($f));
                $ar  = explode(',', $str);
                if (count($ar) !== 12)
                    die("\n$str\n");
                if ($ar[11] === '1') {
                    echo "{$i}:{$str}\n";
                    file_put_contents($fileUuidVipOnly, $ar[2], FILE_APPEND);
                    $i++;
                }
            }
            fclose($f);

        }
    }