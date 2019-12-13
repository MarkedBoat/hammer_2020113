<?php
    defined('ENV_NAME') or define('ENV_NAME', 'debug');


    $dbSlave = [
        'connectionString' => 'mysql:host=bftv_db_read;port=3306;dbname=baofeng_tv',
        'username'         => 'tv',
        'password'         => 'tv_baofeng@2015Inseven',
        'charset'          => 'utf8',
        'readOnly'         => true,
        'attributes'       => [
            \PDO::ATTR_TIMEOUT => 1
        ],
        'slaveConfig'      => [
            'connectionString' => 'mysql:host=bftv_db_read;port=3306;dbname=baofeng_tv',
            'username'         => 'tv',
            'password'         => 'tv_baofeng@2015Inseven',
            'charset'          => 'utf8',
            'attributes'       => [
                PDO::ATTR_TIMEOUT => 10,
            ],

        ]
    ];

    $funtv = [
        'connectionString' => 'mysql:host=192.168.16.140;port=3306;dbname=baofeng_tv',
        'username'         => 'root',
        'password'         => '123456',
        'charset'          => 'utf8',
        'readOnly'         => true,
        'attributes'       => [
            \PDO::ATTR_TIMEOUT => 1
        ]
    ];
    return [
        'db'        => ['cli_bftv_slave' => $dbSlave, 'funtv' => $funtv],
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
            'trial_member_dur' => '7_day',
            'group'            => ['memberAmountMax' => 6, 'applyExpires' => 604800],
            'member'           => [
                'bind1st_had_trial'     => '3_day',
                'bind1st_has_not_trial' => '10_day',
                'bind1st_trial_vers'    => [0 => ['3_day', '10_day'], 389 => ['7_day', '14_day']],
            ],
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
                        'status'    => true,
                        'maxLimit'  => 1,
                        'timeLimit' => 7200,
                        'logstyle'  => ['Y', 'md', '>>'],
                    ],
                ]
            ],

        ],
    ];
