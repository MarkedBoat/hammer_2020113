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
            $phpFile = Sys::app()->params['console']['logDir'] . '/onlined_uuid.csv';
            $goon    = true;
            $i       = 0;
            $lastId  = 0;
            file_put_contents($phpFile, '');
            while ($goon) {
                $table = Sys::app()->db('cli_bftv_slave')->setText("select  id, uuid, getuiid, platform, sys_version, softid, launcher_version,ip, province, city from std_uuid_getuiid and id>'{$lastId}' ORDER BY id ASC LIMIT 1000")->queryAll();
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
    }