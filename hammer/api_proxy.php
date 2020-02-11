<?php

    ob_start();
    function curlRequestPost($url, $params) {
        $user_agent = "kinglone";
        $ch         = curl_init();    // 初始化CURL句柄
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        //curl_setopt($ch, CURLOPT_FAILONERROR, 1); // 启用时显示HTTP状态码，默认行为是忽略编号小于等于400的HTTP信息
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);//启用时会将服务器服务器返回的“Location:”放在header中递归的返回给服务器
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);// 设为TRUE把curl_exec()结果转化为字串，而不是直接输出

        curl_setopt($ch, CURLOPT_POST, 1);//启用POST提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params); //设置POST提交的字符串
        //curl_setopt($ch, CURLOPT_PORT, 80); //设置端口
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // 超时时间
        //设置HTTP头信息
        $time = microtime(true);
        $sign = substr(md5($time . 'kinglone'), 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "web-server-time: $time",
            "web-server-sign: $sign"
        ]);
        $document = curl_exec($ch); //执行预定义的CURL
        $info     = curl_getinfo($ch); //得到返回信息的特性
        $result   = array(
            '__content' => $document,
            '__info'    => $info
        );
        curl_close($ch);
        return $result;
    }

    $requestTime       = time();
    $apiUrl            = 'http://ptbftv.gitv.tv/';
    $params            = $_POST;
    $params['kldebug'] = 'x';
    $content           = curlRequestPost($apiUrl, $_POST);
    $responeTime       = time();
    $result            = $content['__content'];
    $data              = json_decode($result, TRUE);
    $requestDate       = date('Y/m/d H:i:s', $requestTime);
    $responeDate       = date('Y/m/d H:i:s', $responeTime);
    $diff              = $responeTime = $requestTime;
    $paramsBulk        = '';
    $method            = $params['method'];
    $paramsBulk        = '';
    foreach ($params as $k => $v)
        $paramsBulk .= "{$k}:{$v}\n";
    $str = "\n{$method}------------------------------------------\nrequest:{$requestDate}\n" . json_encode($params, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n#########\n{$paramsBulk}\n##########\nrespone:{$responeDate}/{$diff}\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    file_put_contents('/var/log/porter/api_proxy.txt', $str, FILE_APPEND);
    //echo $str;
    ob_end_clean();
    @header('content-Type:application/json;charset=utf8');
    echo $result;




