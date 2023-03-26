<?php


    namespace console\test;

    use models\common\CmdBase;
    use models\common\sys\Sys;

    ini_set('memory_limit', '1024M');

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
            $redis      = new \Redis();
            $redis->connect('127.0.0.1', 6379);
          //  $redis->auth('redis_kinglone');
            //var_dump($redis->info());
           // $redis->set('test',1111);
            $redis->expire('test',5);
            var_dump($redis->get('test'));
            sleep(3);
            var_dump($redis->get('test'));
            sleep(3);
            var_dump($redis->get('test'));

            die;

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
            $fileName = '/data/git-webroot/api-htdocs/Bftvapi/download/log.csv';
            file_put_contents($fileName, '');
            $i    = 0;
            $data = [];
            foreach ($array as $redisName => $cfg) {
                $data[$redisName] = ['dbs' => []];
                echo "redis:{$redisName}\n";
                $countRedis = 0;
                $redis      = new \Redis();
                $redis->connect($cfg['host'], $cfg['port']);
                $redis->auth($cfg['password']);
                $info = $redis->info();
                // var_export($info);die;
                $dbIndexs = [];
                foreach ($info as $key => $str) {
                    if (substr($key, 0, 2) === 'db' && substr($str, 0, 5) === 'keys=') {
                        $dbIndexStr = substr($key, 2);
                        $dbIndex    = intval($dbIndexStr);
                        if ($dbIndexStr === strval($dbIndex)) {
                            $dbIndexs[] = $dbIndex;
                            echo "{$key}:{$str}\n";
                            $data[$redisName]['dbs'][$key] = $str;
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
                        if (is_array($keys))
                            foreach ($keys as $key) {
                                echo "all:{$i}\ttime:" . date('Y-m-d H:i:s', time()) . "\tredis:{$redisName}\tcount:{$countRedis}\tdb:{$db}\tcount:{$countDb}\tkey>{$key}\n";
                                $i++;
                                $countRedis++;
                                $countDb++;
                                file_put_contents($fileName, "{$i},{$redisName},{$db},{$key}\n", FILE_APPEND);

                            }
                    }
                }
                $data[$redisName]['total'] = $countRedis;
            }


            echo "\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
            echo "\nover\n";
            die;
        }

        public function hosts() {
            $filenames = ['/data/upload/urls.txt', '/data/upload/hosts.txt'];
            $strs      = [];
            foreach ($filenames as $filename) {
                $f        = fopen($filename, 'r');
                $fileLine = 0;
                while (!feof($f)) {
                    $fileLine++;
                    $str = trim(fgets($f));
                    if (!in_array($str, $strs))
                        $strs[] = $str;
                    echo "{$fileLine},{$str}\n";
                }

                fclose($f);
            }

            $db        = Sys::app()->db('dev0');
            $strsArray = array_chunk($strs, 1000);
            foreach ($strsArray as $i => $strs) {
                $bind = [];
                $sqls = [];
                foreach ($strs as $j => $str) {
                    $sqls[]            = "insert ignore into tmp_hosts set str=:str_{$j}";
                    $bind[":str_{$j}"] = $str;
                }
                echo "sql:{$i}";
                $db->setText(join(';', $sqls))->bindArray($bind)->execute();
            }
            echo "\nok\n";

        }

        public function fileToArray($fileName, $fn) {
            $array    = [];
            $f        = fopen($fileName, 'r');
            $fileLine = 0;
            while (!feof($f)) {
                $fileLine++;
                $str = trim(fgets($f));
                $fn($array, $str);
            }
            fclose($f);
            return $array;
        }

        public function diffCsv() {
            $file1    = '/data/upload/tmp2.csv';//原数据
            $file2    = '/data/upload/vip_still.csv';//基于file1 还是会员的
            $diff1    = '/data/upload/allvip_uuid.csv';//去年的数据
            $diff2    = '/data/upload/vip_tvsns.20200302';//今天的数据
            $vipTvsns = $this->fileToArray($file1, function (&$array, $str) {
                $ar = explode(',', $str);
                if (count($ar) === 3 && (strtotime($ar[2]) > time()))
                    $array[] = $ar[0];


            });
            $diffs    = [
                ['file' => $diff1, 'result' => 'all_diff_vip.csv'],
                ['file' => $diff2, 'result' => 'today_diff_vip.csv'],

            ];
            foreach ($diffs as $diff) {
                $tvSns      = $this->fileToArray($diff['file'], function (&$array, $str) {
                    if (strlen($str) > 10)
                        $array[] = $str;
                });
                $novipTvSns = array_diff($tvSns, $vipTvsns);
                $filename   = "/data/upload/{$diff['result']}";
                file_put_contents($filename, '');
                foreach ($novipTvSns as $tvSn) {
                    $str = "{$tvSn}\n";
                    echo "{$filename}\t{$str}";
                    file_put_contents($filename, $str, FILE_APPEND);
                }
            }
        }

        public function diffCsv2() {
            $file1    = '/data/upload/tmp2.csv';//原数据
            $diff1    = '/data/upload/allvip_uuid.csv';//去年的数据
            $vipTvsns = $this->fileToArray($file1, function (&$array, $str) {
                $ar = explode(',', $str);
                if (count($ar) === 3)
                    $array[] = $ar[0];
            });
            $cnt0     = count($vipTvsns);
            $vipTvsns = array_unique($vipTvsns);

            $diffs = [
                ['file' => $diff1, 'result' => 'diff_all.csv'],
            ];
            foreach ($diffs as $diff) {
                $tvSns      = $this->fileToArray($diff['file'], function (&$array, $str) {
                    if (strlen($str) > 10)
                        $array[] = $str;
                });
                $cnt1       = count($tvSns);
                $tvSns      = array_unique($tvSns);
                $novipTvSns = array_diff($tvSns, $vipTvsns);
                $filename   = "/data/upload/{$diff['result']}";
                file_put_contents($filename, '');
                foreach ($novipTvSns as $tvSn) {
                    $str = "{$tvSn}\n";
                    //echo "{$filename}\t{$str}";
                    file_put_contents($filename, $str, FILE_APPEND);
                }
                echo "tvSns_diff:" . count($vipTvsns) . ".{$cnt0}\n";
                echo "tvSns_base:" . count($tvSns) . "/{$cnt1}\n";
                echo "tvSns_result:" . count($novipTvSns) . "/#\n";

            }
        }

        public function testCurls() {
            $names = ['bftv.video.detail', 'bftv.video.actorlist', 'bftv.video.videorelated', 'bftv.video.tvlist'];
            foreach ($names as $i => $name)
                $this->testCurl($name, $i);
            foreach ($names as $i => $name)
                $this->testCurl($name, $i, 'no');
        }

        public function testCurl($opt = '', $outLabel = 0, $rand = 'yes') {
            //printf("%3d ddd|%-3d|%10s\n", 1, 2, 'x');
            if (!$opt)
                $opt = $this->params->getStringNotNull('opt');
            $arrs = [
                'namelookup_time: 解析时间， 从开始直到解析完远程请求的时间；',
                'connect_time: 建立连接时间,从开始直到与远程请求服务器建立连接的时间；',
                'pretransfer_time: 从开始直到第一个远程请求接收到第一个字节的时间；',
                'starttranster_time: 从开始直到第一个字节返回给curl的时间；',
                'total_time： 从开始直到结束的所有时间'
            ];
            if ($outLabel === 0) {
                echo "\n";
                foreach ($arrs as $str) {
                    echo ">>\t{$str}\n";
                }
                echo "\n";
            }


            $opts = [

                'bftv.video.detail'       => [
                    'title' => '详情',
                    'url'   => 'http://ptbftv.gitv.tv',
                    'size'  => 50,
                    'post'  => [
                        'apptoken'  => '282340ce12c5e10fa84171660a2054f8',
                        'extend'    => 'desc',
                        'kldebug'   => 'x',
                        'method'    => 'bftv.video.detail',
                        'plateForm' => 'bftv_android',
                        'sn'        => '600000MUB00D168Q3185_3DD1',
                        'time'      => '1583477113244',
                        'tvtoken'   => '40:bc:68:36:ac:ba',
                        'version'   => '1.0',
                        'vid'       => '6861180'
                    ]
                ],
                'bftv.video.actorlist'    => [
                    'title' => '人物列表',
                    'url'   => 'http://ptbftv.gitv.tv',
                    'size'  => 50,
                    'post'  => [
                        'apptoken'  => '282340ce12c5e10fa84171660a2054f8',
                        'kldebug'   => 'x',
                        'method'    => 'bftv.video.actorlist',
                        'plateForm' => 'bftv_android',
                        'sn'        => '600000MUB00D168Q3185_3DD1',
                        'time'      => '1583477113417',
                        'tvtoken'   => '40:bc:68:36:ac:ba',
                        'version'   => '1.0',
                        'vid'       => '6861180'
                    ]
                ],
                'bftv.video.videorelated' => [
                    'title' => '关联视频',
                    'url'   => 'http://ptbftv.gitv.tv',
                    'size'  => 50,
                    'post'  => [
                        'apptoken'  => '282340ce12c5e10fa84171660a2054f8',
                        'extend'    => 'cover',
                        'kldebug'   => 'x',
                        'method'    => 'bftv.video.videorelated',
                        'offset'    => '12',
                        'plateForm' => 'bftv_android',
                        'sn'        => '600000MUB00D168Q3185_3DD1',
                        'time'      => '1583477113420',
                        'tvtoken'   => '40:bc:68:36:ac:ba',
                        'version'   => '1.0',
                        'vid'       => '6861180'
                    ]
                ],
                'bftv.video.tvlist'       => [
                    'title' => '分集列表',
                    'url'   => 'http://ptbftv.gitv.tv',
                    'size'  => 50,
                    'post'  => [
                        'apptoken'  => '282340ce12c5e10fa84171660a2054f8',
                        'kldebug'   => 'x',
                        'method'    => 'bftv.video.tvlist',
                        'page'      => '1',
                        'pageSize'  => '100000',
                        'plateForm' => 'bftv_android',
                        'sn'        => '600000MUB00D168Q3185_3DD1',
                        'time'      => '1583477115607',
                        'tvtoken'   => '40:bc:68:36:ac:ba',
                        'version'   => '1.0',
                        'vid'       => '6861180'
                    ]
                ],
            ];
            if (!isset($opts[$opt]))
                throw  new  \Exception('xxxxx');
            $opt = $opts[$opt];
            echo "\n请求:{$opt['title']}   次数:{$opt['size']} 随机{$rand},url:{$opt['url']} \n";

            $urls = [];
            for ($i = 0; $i < $opt['size']; $i++) {
                if ($rand) {
                    $opt['post']['vid'] = rand(6061180, 6861180);
                } else {
                    $opt['post']['vid'] = 6861180 - $i;
                }
                $urls[] = [
                    'key'    => $i,
                    '__url'  => $opt['url'],
                    '__post' => isset($opt['post']) ? $opt['post'] : false
                ];
            }


            $ts1         = time();
            $rows        = $this->getUrls($urls);
            $ts2         = time();
            $diff        = $ts2 - $ts1;
            $cnt         = count($urls);
            $cntFailed   = 0;
            $totalTimes  = [];
            $dnsTimes    = [];
            $conectTimes = [];
            $preTimes    = [];
            $startTimes  = [];
            $phpTimes    = [];
            $ips         = [];

            // printf("%15s|%-20s|%-20s|%-20s|%-20s|%-20s|%-20s|%-20s|%-100s\n", '>>order', 'total_time', 'namelookup_time', 'connect_time', 'pretransfer_time', 'starttransfer_time', 'php_count', 'result', 'info');
            foreach ($rows as $i => $row) {
                $msg     = '';
                $phpTime = 0;
                if ($row['__error'] === false) {
                    $times[]       = $row['__info']['total_time'];
                    $totalTimes[]  = $row['__info']['total_time'];
                    $dnsTimes[]    = $row['__info']['namelookup_time'];
                    $conectTimes[] = $row['__info']['connect_time'] - $row['__info']['namelookup_time'];
                    $preTimes[]    = $row['__info']['pretransfer_time'] - $row['__info']['connect_time'];
                    $startTimes[]  = $row['__info']['starttransfer_time'] - $row['__info']['pretransfer_time'];
                    $data          = json_decode($row['__content'], true);
                    if (is_array($data)) {
                        if (isset($data['data']['cost'])) {
                            $phpTime = $data['data']['cost'];

                        } else {
                            $msg     = isset($data['msg']) ? $data['msg'] : (isset($data['error_msg']) ? $data['error_msg'] : '');
                            $phpTime = 0;
                        }
                    } else {
                        $msg     = 'result is not array';
                        $phpTime = 0;
                    }
                    $phpTimes[] = $phpTime;
                } else {
                    $msg = $row['__error'];
                    $cntFailed++;
                }
                if ($row['__info']['primary_ip'])
                    $ips[] = $row['__info']['primary_ip'];
                //printf("%15s|%-20f|%-20f|%-20f|%-20f|%-20f|%-20f|%-20s|%-100s\n", $i, $row['__info']['total_time'], $row['__info']['namelookup_time'], $row['__info']['connect_time'] - $row['__info']['namelookup_time'], $row['__info']['pretransfer_time'] - $row['__info']['connect_time'], $row['__info']['starttransfer_time'] - $row['__info']['pretransfer_time'], $phpTime, $row['__info']['total_time'] > 0.5 ? 'yes' : '', $msg);

            }
            printf(">>%-15s|%-20s|%-20s|%-20s|%-20s|%-20s|%-20s|%-20s|%-s\n", '', 'total_time', 'namelookup_time', 'connect_time', 'pretransfer_time', 'starttransfer_time', 'php_count', 'result', 'info');
            rsort($times);
            rsort($totalTimes);
            rsort($dnsTimes);
            rsort($conectTimes);
            rsort($preTimes);
            rsort($startTimes);
            rsort($phpTimes);

            printf(">>%15s|%-20f|%-20f|%-20f|%-20f|%-20f|%-20f|%-20s|%-s\n", 'max', $totalTimes[0], $dnsTimes[0], $conectTimes[0], $preTimes[0], $startTimes[0], $phpTimes[0], '', '各类时间最长');

            printf(">>%15s|%-20f|%-20f|%-20f|%-20f|%-20f|%-20f|%-20s|%-s\n", 'min', $totalTimes[count($totalTimes) - 1], $dnsTimes[count($dnsTimes) - 1], $conectTimes[count($conectTimes) - 1], $preTimes[count($preTimes) - 1], $startTimes[count($startTimes) - 1], $phpTimes[count($phpTimes) - 1], '', '各类时间最小');

            printf(">>%15s|%-20f|%-20f|%-20f|%-20f|%-20f|%-20f|%-20s|%-s\n", 'avg', array_sum($totalTimes) / count($totalTimes), array_sum($dnsTimes) / count($dnsTimes), array_sum($conectTimes) / count($conectTimes), array_sum($preTimes) / count($preTimes), array_sum($startTimes) / count($startTimes), array_sum($phpTimes) / count($phpTimes), '', '各类时间平均');

            $success = count($times);


            printf(">>%15s|%-50s\n", 'total', $cnt);
            printf(">>%15s|%-50s\n", 'failed', $cntFailed);
            printf(">>%15s|%-50s\n", 'success', $success);
            printf(">>%15s|%-50s\n", 'ips', join(',', array_unique($ips)));
            $arrs = [
                'namelookup_time: 解析时间， 从开始直到解析完远程请求的时间；',
                'connect_time: 建立连接时间,从开始直到与远程请求服务器建立连接的时间；',
                'pretransfer_time: 从开始直到第一个远程请求接收到第一个字节的时间；',
                'starttranster_time: 从开始直到第一个字节返回给curl的时间；',
                'total_time： 从开始直到结束的所有时间'
            ];
            echo "\n";
            foreach ($arrs as $str) {
                // echo ">>\t{$str}\n";
            }
            echo "\n";
        }

        public function getUrls($urls, $opt = []) {
            $chs  = array();
            $rows = array();
            if (!$opt)
                $opt = array(
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11',
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_FOLLOWLOCATION => 1,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_FAILONERROR    => 0,
                    CURLOPT_AUTOREFERER    => 1,
                    CURLOPT_TIMEOUT        => 50,
                    CURLOPT_HEADER         => FALSE,
                    CURLOPT_RETURNTRANSFER => TRUE,
                    CURLOPT_HTTPHEADER     => array(),
                    // CURLOPT_POST           => 1, // 发送一个常规的Post请求
                );
            foreach ($urls as $key => $row) {
                $url = is_array($row) ? $row['__url'] : $row;
                $ch  = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_REFERER, $url);
                if ($row['__post']) {
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $row['__post']);
                }

                curl_setopt_array($ch, $opt);
                $chs[$key]  = $ch;
                $rows[$key] = is_array($row) ? $row : array('__url' => $url);

            }
            $mh = curl_multi_init();
            foreach ($chs as $i => $ch) {
                curl_multi_add_handle($mh, $ch);
            }
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active and $mrc == CURLM_OK) {
                //代替下面被注释的，防止 cpu使用过高
                if (curl_multi_select($mh) === -1) {
                    usleep(100);
                }
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
            foreach ($chs as $i => $ch) {
                $info                  = curl_getinfo($ch);
                $rows[$i]['__error']   = in_array($info['http_code'], [
                    200,
                    400
                ]) ? false : $info['http_code'] . '#' . curl_errno($ch) . '#' . curl_error($ch);
                $content               = curl_multi_getcontent($ch);
                $rows[$i]['__content'] = $content;
                $rows[$i]['__info']    = $info;
                curl_close($ch);
                curl_multi_remove_handle($mh, $ch);
            }
            curl_multi_close($mh);
            return $rows;
        }

        public function mids(){
            $ids_fail='25153331,25153345,25153353,25153437,25153769,25153817,25153823,25155701,25155771,25155789,25158121,25160557,25161015,25161045,25161051,25161523,25162597,25162641,25162659,25163583,25167045,25167055,25167115,25167121,24748515,24790853,25153301,25153321,24248409,24248413,24248415,24248423,24248433,24248969,24248971,24249005,24424239,24647493,53846235,53846237,25167139,25167179,25167649,25282057,26037789,26089991,24644275,53846233,53846243,53846259,44092941,53781387,53781431,26089995,26089999,26444593,26465853,26469737,26641379,26641381,29919159,29919831,29935123,29935165,32075777,32123859,32130129,32136571,32156723,32160239,32208475,32208477,32264407,32363253,32364563,32398885,32407617,32425669,32563579,32564859,32639707,32660241,32716993,32717003,32717009,32717023,32717051,32717053,32841609,32878817,32894595,32921629,32932105,32936307,32942307,32942313,32961891,32973681,33012865,33028615,42128581,42980929,43386883,43389265,43413367,43478677,47101369,47101383,47101387,47101393,47101395,47101399,47101405,47220803,47220807,47240935,47240941,53709243,53709255,53709589,53709961,53711247,53714131,53714169,53714177,53714329,53715773,53715811,53715931,53718779,53718883,53724933,53724961,53730479,53738663,53742883,53743289,53744651,53745575,54103509,54103517,54103527,54103553,54104041,54104057,54104065,54104093,54116761,54124423,54124459,54124977,54125011,54125057,54125197,54125265,54125275,54152483,59002217,59002229,59002235,59002239,59002297,59002307,59002319,59002333,59002373,59002397,59067167,59156421,59159681,59345971,59350619,33494577,33495219,33495363,33529783,33529843,33529849,33539783,33542007,33542799,33547073,33547125,33547155,33547161,33547181,33549309,39637943,43387555,43387709,43406671,43407175,43486905,43497347,25167635,29369167,29513841,29843373,29843379,29867877,30119749,30119775,30205173,30304153,30454513,30847987,30873713,30921009,31073261,31091139,31167743,31182217,31389143,31399707,31454059,31477591,31536513,31608393,31775069,31785981,31813151,31854685,31873471,31900927,31929521,31962859,31984321,31987817,31991923,32028531,32028573,32044011,32101661,47101213,47101219,47101221,47101223,47101227,47101325,47101329,47101335,47101351,47101357,47101367,53602319,53606383,53641757,53675659,53684499,53685665,53685733,53687305,53692725,53694641,53695111,53700197,53700199,53700207,53700235,53703685,53703697,53703709,53703713,53709211,53709213,53709219,54017733,54054739,54070869,54071055,54071105,54071165,54071243,54071271,54103305,54103311,54103317,54103411,54103425,54103451,54103457,54103461,54103469,58786703,58786777,58786803,58786821,58786823,58786829,58786833,58786853,58786921,58786929,58787145,58787195,58787265,58787305,58787317,58787331,58787349,58787443,58850487,58976319,59002205,43796493,44191075,44270935,44309637,44317161,44333415,44342531,44361229,44361313,44361331,44361355,44371151,44391981,44400795,44408455,44408465,44446579,44467591,44516299,44541017,44542989,44589299,29524581,29843341,26707521,26801341,26804789,26804887,26811991,26975823,27037161,27053505,27054393,27102501,27106901,27219301,27368841,27384587,27422499,27480097,27583281,27649089,27728585,27742503,27952431,27960441,27960459,27960475,28012005,28206161,28358003,28358007,28682845,28740379,29315159,29843327,29843329,29843335,29843337,31814953,31930133,31930171,31931157,31932937,33549943,33550611,33550681,33550725,33551273,33554141,45557379,47101195,47101199,47101209,47101211,49086709,49109309,49118603,49134691,49161349,49176035,49191225,49203995,49208267,49209593,49210657,49213671,53382221,53382309,53382391,53382891,53393817,53393819,53426269,53454167,53466297,53552697,53973995,53974001,53974015,53974023,53974081,53974105,53974123,53979141,53985817,53986339,53986433,53986453,53986519,53986523,53986671,53986689,53986817,58785697,58785705,58785727,58785791,58785795,58785813,58785845,58785867,58785879,58785933,58786007,58786035,58786145,58786149,58786151,58786165,58786231,58786349,58786419,58786533,58786651,44608213,44608219,44621639,44621733,44621769,44653677,44700159,44709167,44726865,44735461,44750019,44769831,44895619,44901925,44906985,44987931,44996599,45008919,45020307,45028565,45028967,45046555,25946531,25946547,25947151,25951569,25959339,26003561,26051357,26071769,26196895,26214125,26223517,26223551,26224609,26250219,26293457,26344453,26412975,26460995,26463961,26467437,26492871,26521145,26555323,26627547,26638901,26646327,28375775,29843297,29843313,29843321,31802277,31802289,31802293,31802297,31809311,33545283,33545335,33545337,33545375,33547195,33547461,33547543,33547553,33547585,33548029,33549903,49026033,49030223,49035241,49035789,49038863,49040399,49040401,49040403,49043271,49046531,49046573,49047917,49052929,49054159,49055681,49057267,49057271,49057763,49077189,49077191,49077209,49086287,53954367,53954373,53954389,53954467,53970799,53970811,53970837,53970843,53970909,53971019,53971135,53971137,53971173,53971195,53971233,53971255,53971347,58392523,58393007,58424685,58469001,58565513,58586859,58603269,58626509,58628409,58673165,58673171,58760555,58768427,58785333,58785349,58785437,58785515,58785547,58785605,58785607,58785667,24082687,45092339,45132065,45174823,45487517,45518049,45542197,45564223,45585235,45592405,45596123,45651653,45658725,45764895,45785595,45867005,45937971,46017961,46162191,46243765,46254503,47262937,25940843,25940875,25941637,25942105,25942913,24536609,24671909,24922545,24933281,25102633,25116163,25142807,25170141,25196969,25235243,25335559,25384395,25396495,25492701,25518949,25555761,25556375,25575845,25582647,25612233,25757593,25763739,25808083,25816451,25885615,25937391,25938169,25939175,25939487,25939503,25940399,28375717,28375719,28375729,28375755,31790557,31790559,31790569,31790573,31801839,33541095,33541589,33542593,33544925,33544965,33545009,33545019,33545073,33545127,33545173,33545263,53934031,53934047,53937911,53940017,53940029,53952113,53952141,53952287,53952295,53952305,53952529,53954131,53954157,53954279,53954311,53954347,53954349,58252321,58252387,58262767,58262791,58262823,58262851,58262937,58262975,58262977,58262989,58263011,58263055,58263109,58263141,58263145,58263147,58263151,58263199,58381825,58390889,58391409,58998315,58998333,58998337,58998403,58998533,59011759,59020035,59020069,59046755,59048569,59050953,59056509,59057553,59059977,59167499,59187907,59205433,59208091,59217947,59223831,59237563,59392147,24140313,24455127,24456287,24457007,24457805,24458563,24458573,24459197,24459201,24459353,24459985,24460011,24462323,24462385,24462395,24467529,24468129,24470335,24470795,24481779,24482207,24482285';
            $ids_ok='26764379,26774565,26931633,26955551,26959995,26984471,27086625,27143633,27199361,27222135,27385211,27408339,27418379,27453137,27510609,27610287,27810615,27995305,28122503,28123925,28157733,28161967,30134137,30134147,30134165,30134167,33431403,33436233,33438663,35537631,35826409,35843911,36231317,36780855,36783219,36798719,36817791,36817809,36820027,36823907,36823915,36823927,36823959,36917335,36917343,37016147,37016213,37016219,37027641,37027657,37027699,37027723,37155697,37176157,37176173,37193391,37193437,37193445,37193463,37197397,37216429,37216463,37246207,37247547,44708065,44726241,44731407,44765083,44769093,44769449,44769475,44776661,44812543,44890137,44898549,44898667,44905515,44905519,44905529,44905551,44906833,44906851,44906855,44911497,54082691,54092507,54093549,54098283,54098621,54108117,54108127,54116439,54119717,54126897,54130271,54130563,54148217,54185993,54186093,54189037,54197981,54212855,54212859,54218329,54218337,54218339,54218341,54218343,54218345,54218347,54218351,54218441,55095071,55095077,55111809,55111839,55111921,55132467,55136023,55136035,55148503,55148509,55148595,55150189,55150197,55150373,55178607,55180833,55180835,55180845,26099563,26106105,26106113,26118157,26150015,26177737,26177739,26248787,26278589,26292785,26328387,26388117,26477975,26477999,26478001,26648179,26694681,26700385,30056313,30056331,30057733,30134131,33339325,33340221,33390757,33397119,33431153,33691345,35693737,36503719,36512795,36564465,36572313,36572319,36572331,36572339,36572379,36592023,36592029,36596081,36612609,36634447,36676271,36682055,36684893,36685045,36689017,36689027,36689035,36689047,36689057,36689061,36689063,36689073,36690961,36709285,36709287,36712001,36714657,36722185,44549489,44551467,44551469,44567827,44567829,44581239,44581245,44581253,44599067,44599077,44624779,44668335,44677945,44677951,44678437,44681011,44694079,53972553,53972569,53973979,53975341,53984121,53984129,53984203,53989319,53990461,53990589,53992401,53999191,54000899,54027185,54035199,54040079,54045697,54045851,54054893,54064867,54081603,54081607,54106253,54106259,54199969,54212839,54212843,54212851,54986109,54986111,54986121,54999875,55020213,55035081,55045687,55045689,55045699,55045701,55062933,55062935,55062943,55079925,55081421,55081431,55081435,55095069,44668337,44668341,44668377,44668459,25222625,25226619,25256859,25256861,25273971,25395409,25471447,25483669,25660987,25770499,25779139,25788661,25788669,25860071,25978785,26068215,30023415,30056273,30056275,30056279,33327455,33328377,33328385,33329589,33338573,36060311,36080095,36080637,36113071,36145623,36145627,36145651,36158007,36224743,36230231,36240385,36259577,36275375,36302701,36304231,36306303,36332183,36337327,36340691,36340705,36340737,36378651,36422183,36425761,36428363,36480113,36490109,36490115,36490117,36491501,36493349,44528575,44528583,44528585,44531835,44531837,44531839,44531841,44532067,44539037,44539039,44539047,44540193,44540209,44541109,44541271,44541287,44543995,44547903,44547905,44547907,44547913,53900061,53924027,53924041,53943879,53946107,53956027,53956119,53960853,53962781,53962857,53964769,53964771,53964775,53964779,53970665,53972283,53972285,53972297,53972303,53972379,53972397,53972501,53972543,53998079,53998083,53999079,53999083,53999181,53999185,53999187,53999189,54399953,54400049,54406473,54406517,54417039,54426037,54426127,54426265,54476085,54543397,54746351,54771663,54862577,54890053,54907275,54986095,54986101,25213003,29991935,29996551,29997645,30023413,33199905,33216977,33301223,33324925,33324999,35621343,35621353,35621355,35626439,35685157,35701293,35710925,35744815,35819817,35820697,35829803,35829805,35853755,35853759,35882327,35882331,35882349,35914901,35953147,35955167,35955173,35975229,35975241,35975253,35975255,35987163,35988617,35988627,36028843,36028845,36028865,44386493,44386519,44393807,44394733,44395773,44395937,44395951,44396307,44406031,44408519,44413381,44442957,44442965,44451659,44492827,44505289,44505293,44505303,44505313,44524063,44528573,53705255,53713815,53800439,53808077,53810233,53823101,53823103,53823105,53823111,53849379,53891581,53915679,53915705,53915929,53916077,53917305,53917389,53919193,53919245,53927571,53927597,53936385,53937385,53939011,53941139,53941411,53941825,53941955,53941965,53942031,53943841,54368483,54368571,54368667,54368709,54368871,54368915,54368949,54369259,54369275,54369525,54369691,54369725,54369737,54372679,54373343,54389591,54398553,24659661,24660029,24704265,24729255,24731719,24732483,24735127,24781433,24781447,24807321,24811369,24875667,24880995,24892261,24935835,24949903,25126497,25126503,25126507,25137657,29949809,29978917,29989641,29989655,32949977,32950017,32950021,32965165,32982451,34794965,34833067,34842721,34999109,35014895,35024091,35040213,35044133,35044141,35044215,35044987,35045283,35092211,35135573,35155143,35213353,35236899,35276205,35302495,35308807,35330693,35360299,35389547,35426267,35426269,35426273,35467951,35504485,35540383,35553813,35578321,44112843,44112849,44112865,44112879,44113027,44113049,44113059,44113063,44113089,44113255,44113275,44266633,44307757,44332251,44359877,44370493,44370597,44370789,44372693,44380769,44386491,53686449,53686805,53687037,53689549,53689629,53689633,53705215,53705233,53705235,53705239,53705241,53860751,53862837,53877049,53878235,53879329,53882277,53883569,53886671,53888439,53888491,53888619,53890427,53904069,53910043,53910141,53910175,53910183,53910187,53910269,53913121,54315549,54315575,54315697,54315727,54315901,54315937,54328837,54350461,54350491,54350543,54350857,54350859,54367455,54367553,54367577,54367727,54367741,24115467,24130035,24200003,24201151,24250467,24350747,24407123,24410307,24478313,24491869,24521595,24607951,24651523,29936307,29936321,29936339,29936349,32368163,32373181,32386955,32390613,32949961,34117269,34143363,34145779,34151245,34188575,34195361,34195775,34198351,34308439,34319967,34372533,34390419,34399379,34442319,34447811,34479357,34482011,34484085,34486239,34486711,34532309,34548069,34566101,34605251,34613219,34642455,34657623,34659561,34707211,34763719,34778081,44112103,44112117,44112125,44112147,44112311,44112313,44112327,44112333,44112497,44112611,44112613,44112615,44112617,44112633,44112637,44112681,44112685,44112819,44112825,44112827,44112831,47241119,47241129,53407903,53407905,53407907,53407913,53605611,53640705,53666561,53666563,53666577,53790059,53790421,53794751,53794891,53796029,53828839,53829737,53830097,53830539,53832527,53839391,53840513,53842529,53843791,53849331,54314575,54314587,54314637,54314787,54314843,54314853,54314859,54315025,54315027,54315089,54315231,54315243,54315247,54315289,54315305,54315437,54315451,21853151,22892501,23983379,23997767,23997771,23997775,23997777,23997789,23997791,23997793,23997853,23997865,23998241,23998247,23998329,23998973,24018659,24023435,24034727,24078647,24083041,24083247,24094035,29935201,29935213,29935297,29936301,32208505,32348239,32359759,32366929,32368155,33032581,33040529,33042143,33076453,33086147,33098253,33139091,33203331,33228011,33252673,33364425,33380757,33397805,33432279,33465489,33477181,33493381,33503157,33516157,33516161,33601053,33601939,33619819,33680763,33725267,33747761,33762829,33889439,34059943,34080205,34108853,43478685,43479581,43486273,43552645,43560059,43771903,43771909,43773349,43783879,43821931,43859323,44078029,44080455,44084565,44111559,44111703,44111715,44111737,44111741,44111925,47240951,47241017,47241021,47241027,47241045,47241057,47241085,47241093,47241107,47241109,47241115,53746435,53748981,53748987,53750971,53750973,53750977,53751029,53751323,53763185,53763193,53763201,53765089,53765399,53784237,53785549,53786947,53789293,53789397,53789399,54152519,54152935,54169349,54169455,54169465,54169499,54189427,54189955,54190023,54200465,54200515,54200525,54271963,54280217,54280483,54283223,54284835,54314563,29369167';
            $ids_fail=explode(',',$ids_fail);
            $ids_ok=explode(',',$ids_ok);
            $ids=array_diff($ids_fail,$ids_ok);
            echo "\n";
            $str= join("\n",$ids);
            echo $str;
            file_put_contents('/data/upload/mids.csv',$str);
            echo "\n";


        }
    }