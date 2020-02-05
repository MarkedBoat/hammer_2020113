<?php


    namespace console\dev\statis;

    use models\common\CmdBase;
    use models\common\sys\Sys;
    ini_set('memory_limit', '2048M');


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
        public function import() {
            $db       = Sys::app()->db('dev0');
            $csv      = '/data/upload/log.csv';
            $f        = fopen($csv, 'r');
            $fileLine = 0;
            $rows     = [];
            while (!feof($f)) {
                $fileLine++;
                $str        = trim(fgets($f));
                $uuidsVip[] = $str;
                echo $fileLine . '#' . date('H:i:s', time()) . '#' . $str . "\n";
                $ar = explode(',', $str);
                if (count($ar) === 4) {
                    $rows[] = $ar;
                    if (count($rows) === 1000) {
                        $this->insertDb($db, $rows);
                        $rows = [];
                    }
                }
            }
            if (count($rows)) {
                $this->insertDb($db, $rows);
                $rows = [];
            }
            fclose($f);
        }

        public function insertDb($db, $rows) {
            $sqls = [];
            $bind = [];
            foreach ($rows as $i => $row) {
                $sqls[]          = "insert ignore into tmp set redis='{$row[1]}',db_index={$row[2]},k=:k_{$i}";
                $bind[":k_{$i}"] = $row[3];
            }
            $db->setText(join(';', $sqls))->bindArray($bind)->execute();

        }


    }