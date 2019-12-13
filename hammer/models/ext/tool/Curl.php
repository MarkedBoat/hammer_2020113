<?php

    namespace models\ext\tool;
    class Curl {
        static public function curlPostSafeUrl($url, $params) {
            $agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11';
            $curl  = curl_init();
            // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $agent);
            // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1);
            // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0);
            // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 获取的信息以文件流的形式返回
            $tmpInfo = curl_exec($curl);
            // 执行操作
            if (curl_errno($curl)) {
                echo 'Errno:' . curl_error($curl);
                //捕抓异常
                return false;
            } else {
                curl_close($curl);
                // 关闭CURL会话
                return $tmpInfo;
                // 返回数据
            }
        }

        static public function curlGetSafeUrl($url, &$info = []) {
            $agent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.1 Safari/537.11';
            $curl  = curl_init();
            // 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url);
            // 要访问的地址
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            // 对认证证书来源的检查
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
            // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $agent);
            // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
            // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
            // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, false);
            // 发送一个常规的Post请求
            //curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
            // Post提交的数据包
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0);
            // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 获取的信息以文件流的形式返回

            //header头里面写点玩意
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer access_token'));

            $tmpInfo = curl_exec($curl);
            $info    = curl_getinfo($curl);
            // 执行操作
            if (curl_errno($curl)) {
                echo 'Errno:' . curl_error($curl);
                //捕抓异常
                return false;
            } else {
                curl_close($curl);
                // 关闭CURL会话
                return $tmpInfo;
                // 返回数据
            }
        }
    }

    ?>