<?php
$pdo = null;
try{
    $dsn = 'mysql:host=127.0.0.1;dbname=seckill_demo;';
    $pdo = new PDO($dsn,'root','root');
}catch(Exception $e){
    exit($e->getMessage());  
}

