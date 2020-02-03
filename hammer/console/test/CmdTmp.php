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

            $key   = [
                'bin' => ['host' => 'redis-node02', 'port' => 6389, 'password' => 'Ab-18upTxsmuzsf'], 'pay' => ['host' => 'redis-node03', 'port' => 6389, 'password' => 'on4PshJqmibi^2n'], 'mpr' => ['host' => 'redis_mpr', 'port' => 6389, 'password' => 'Cipa6hd0ev^vkCh'],
            ];
            $redis = new \Redis();
            $redis->connect('redis-node02', 6389);
            $redis->auth('Ab-18upTxsmuzsf');

            $iterator = null;
            $i        = 1;
            while (true) {
                $keys = $redis->scan($iterator);
                if ($keys === false) {//迭代结束，未找到匹配pattern的key
                    return;
                }
                foreach ($keys as $key) {
                    echo date('Y-m-d H:i:s', time()) . ">" . $key . "\n";
                }
            }
            echo "\nover\n";
        }
    }