<?php 
require dirname(__DIR__) . '/vendor/autoload.php';

use tong\im\ImServer;

$mysqlConfig = [
    'host'      => '127.0.0.1', //服务器地址
    'port'      => 3306,    //端口
    'user'      => 'root',  //用户名
    'password'  => '000000',  //密码
    'charset'   => 'utf8mb4',  //编码
    'database'  => 'im',  //数据库名
    'prefix'    => '',  //表前缀
    'poolMin'   => 5, //空闲时，保存的最大链接，默认为5
    'poolMax'   => 1000,    //地址池最大连接数，默认1000
    'clearTime' => 60000, //清除空闲链接定时器，默认60秒，单位ms
    'clearAll'  => 300000,  //空闲多久清空所有连接，默认5分钟，单位ms
    'setDefer'  => true,     //设置是否返回结果,默认为true,
];
$server = new ImServer($mysqlConfig);
$server->start();