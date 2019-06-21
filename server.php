<?php

//建立数据库连接
$db_user = 'root';
$db_pass = 'root';
$dbh = new PDO('mysql:host=192.168.91.71;dbname=swoole_chat', $db_user, $db_pass);


$server = new swoole_websocket_server("0.0.0.0", 9502);
$server->on('open', function($server, $req) {
    echo "connection open: {$req->fd}\n";

});

$server->on('message', function($server, $frame) use($dbh) {

    echo "received message: {$frame->data}\n";
    $time = time();

    //消息入库
    $sql = "insert into chat_message (`msg`,`add_time`)  values ('{$frame->data}',$time)";
    $dbh->exec($sql);
    $errcode = $dbh->errorCode();
    $errinfo = $dbh->errorInfo();

    //检测sql错误
    if($errcode !== '00000' ){
        echo 'Errcode:' . $errcode;echo "\n";
        echo 'Message: '.$errinfo[2];
        exit();
    }
    $server->push($frame->fd, $frame->data);
});

$server->on('close', function($server, $fd) {
    echo "connection close: {$fd}\n";
});

$server->start();