<?php
ini_set('memory_limit', '2048M');

$str='ddahjgadhsgds;bnabajgaergiagjrsgheguaeggjaeghra;hagagagjdigjhwgjawgpnasklghghj';
for($i=0;$i<50;$i++){
    $len=strlen($str);
    echo "{$i}/{$len},";
    $str.=$str;
}
$redis=new Redis();
$redis->connect('127.0.0.1',6379);
$key='tttttttttttttttttttttttttttttttttttttttttttttttt';
die;
for($i=0;$i<10000;$i++){
    $r=0;
    try
    {
        $r=intval($redis->set($key.$i,$str,3600));
    }catch (Exception $e){

    }

    echo "{$r}/{$i}\n";
}
die;
echo phpinfo();