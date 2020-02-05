<?php
    defined('ENV_NAME') or define('ENV_NAME', 'dev0');


    $dev0 = [
        'connectionString' => 'mysql:host=loccal;port=3306;dbname=sys',
        'username'         => 'root',
        'password'         => '?rOBo*uKr4:w',
        'charset'          => 'utf8',
        'readOnly'         => true,
        'attributes'       => [
            \PDO::ATTR_TIMEOUT => 1
        ],
        'slaveConfig'      => [
            'connectionString' => 'mysql:host=loccal;port=3306;dbname=sys',
            'username'         => 'root',
            'password'         => '?rOBo*uKr4:w',
            'charset'          => 'utf8',
            'attributes'       => [
                PDO::ATTR_TIMEOUT => 10,
            ],

        ]
    ];

    return [
        'db'        => [
            'dev0' => $dev0,

        ],
        'redis'     => [
            'sl2' => ['host' => 'redis-node02', 'port' => 6389, 'password' => '', 'db' => 10],
        ],
        'memcached' => [
            ['memcache.server1', 11211],
            ['memcache.server2', 11211],
            ['memcache.server3', 11211],
            ['memcache.server4', 11211],
        ],
        'params'    => [
            'debugSign'        => 'debug',
            'errorHttpCode'    => 200,
            'console'          => [
                'phpFile'   => '/usr/bin/php',
                'hammerDir' => '/data/code/porter/hammer',
                'logDir'    => '/var/log/porter',
                'tasks'     => [

                    'appletNotifyPayed'        => [
                        'time'      => ['*/1 */1 */1'],
                        'cmd'       => 'system/queue call  queue=applet_notify_payed  class=\\\console\\\applet\\\notify\\\Payed --method=push',
                        'comment'   => '微信推送 订单支付',
                        'status'    => false,
                        'maxLimit'  => 1,
                        'timeLimit' => 7100,
                        'logstyle'  => ['Ymd', '', '>>'],
                    ],
                    'appletNotifyClientOnline' => [
                        'time'      => ['*/1 */1 */1'],
                        'cmd'       => 'system/queue call  queue=applet_notify_client_online  class=\\\console\\\applet\\\notify\\\ClientOnline --method=push',
                        'comment'   => '设备上线通知',
                        'status'    => false,
                        'maxLimit'  => 1,
                        'timeLimit' => 7100,
                        'logstyle'  => ['Ymd', '', '>>'],
                    ],
                    'slave_to_funtv'           => [
                        'time'      => ['*/1 */1 */1'],
                        'cmd'       => 'mysql/tmp slave_to_funtv',
                        'comment'   => '复制数据 bftv slave => funtv dev',
                        'status'    => false,
                        'maxLimit'  => 1,
                        'timeLimit' => 7200,
                        'logstyle'  => ['Y', 'md', '>>'],
                    ],
                    'git_deploy'               => [
                        'time'      => ['*/1 */1 */1'],
                        'cmd'       => 'system/project scan',
                        'comment'   => '代码部署',
                        'status'    => true,
                        'maxLimit'  => 1,
                        'timeLimit' => 7200,
                        'logstyle'  => ['Y', 'md', '>>'],
                    ],
                    'kill_plan_by_id'          => [
                        'time'      => ['*/1 */1 */1'],
                        'cmd'       => 'system/launcher killPlan',
                        'comment'   => '临时杀死任务',
                        'status'    => true,
                        'maxLimit'  => 1,
                        'timeLimit' => 7200,
                        'logstyle'  => ['Y', 'md', '>>'],
                    ],
                ]
            ],

        ],
    ];
