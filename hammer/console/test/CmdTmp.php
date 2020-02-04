<?php


    namespace console\test;

    use models\common\CmdBase;


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
        public function test() {
            echo "\nok\n";

            $array = [
                //'ali' => ['host' => 'r-2zee8444844418a4.redis.rds.aliyuncs.com', 'port' => 6379, 'password' => 'funshion123!@#'],
                'bin' => ['host' => 'redis-node02', 'port' => 6389, 'password' => 'Ab-18upTxsmuzsf'],
                'pay' => ['host' => 'redis-node03', 'port' => 6389, 'password' => 'on4PshJqmibi^2n'],
                'mpr' => ['host' => 'redis_mpr', 'port' => 6389, 'password' => 'Cipa6hd0ev^vkCh'],
            ];
            $redis = new \Redis();
            //   $redis->connect('redis-node02', 6389);
            //   $redis->auth('Ab-18upTxsmuzsf');
            //   $redis->select(10);
            /*
            $r=$redis->info();
            echo "\n" . json_encode($r, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            var_dump($r);
            var_dump($redis->dbSize());
            die;*/
            $i = 0;
            foreach ($array as $redisName => $cfg) {
                echo "redis:{$redisName}\n";
                $countRedis = 0;
                $redis      = new \Redis();
                $redis->connect($cfg['host'], $cfg['port']);
                $redis->auth($cfg['password']);
                $info     = $redis->info();
               // var_export($info);die;
                $dbIndexs = [];
                foreach ($info as $key => $str) {
                    if (substr($key, 0, 2) === 'db' && substr($str, 0, 5) === 'keys=') {
                        $dbIndexStr = substr($key, 2);
                        $dbIndex    = intval($dbIndexStr);
                        if ($dbIndexStr === strval($dbIndex)) {
                            $dbIndexs[] = $dbIndex;
                            echo "{$key}:{$str}\n";
                        }
                    }
                }
                echo "\n";
                //echo "\n" . json_encode($redis->info(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                // continue;
                foreach ($dbIndexs as $db) {
                    echo "redis:{$redisName}/db:{$db}\n";
                    $redis->select($db);
                    $iterator = null;
                    $countDb  = 0;
                    $goon     = true;
                    while ($goon) {
                        $keys = $redis->scan($iterator);
                        if ($keys === false) {//迭代结束，未找到匹配pattern的key
                            $goon = false;
                        }
                        foreach ($keys as $key) {
                            echo "all:{$i}\ttime:" . date('Y-m-d H:i:s', time()) . "\tredis:{$redisName}\tcount:{$countRedis}\tdb:{$db}\tcount:{$countDb}\tkey>{$key}\n";
                            $i++;
                            $countRedis++;
                            $countDb++;
                        }
                    }
                }
            }


            echo "\nover\n";
        }
    }