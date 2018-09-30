<?php

/**
 * 1. 创建商品订单
 * 2. 加入到缓存中
 */
require 'redis.php';
require 'pdo.php';

 $goods = [
     '花露水 100ml',
     'MacBook pro 13寸',
     'iPhone x 高配版',
     '矿泉水 500ml',
     '小型电风扇 10寸'
 ];


 $goods_stock = 10; //商品库存数量
 


    $list_length = $redis->llen('seckill_list');
    echo "->仅剩下".($goods_stock-$list_length)."件商品啦！！！\n";
    if($list_length >= $goods_stock){
        echo "Done:当前商品已经没有库存了!\n";
        echo "Done:秒杀结束!\n";
    }
    $order = [];
    $user_id = mt_rand(5000,100000);
    $order['goods'] = $goods[mt_rand(0,count($goods) - 1)];
    $order['ordersn'] = create_ordersn();
    $order['status'] = 0;
    $order['queue'] = 0;
    $order['user_id'] = $user_id;
    $orderid = insert_data($pdo,$order);
    if($orderid > 0){
        //加入到redis队列   
        $redis->lpush('seckill_list',"orderid_".$orderid); 
        echo "->用户(".$user_id.") 订单：orderid_".$orderid." 已加入秒杀处理队列!\n";   
    }



 function create_ordersn(){
     $key = 'd7bgf9ds5';
     return md5($key.date('YmdHis',time()).mt_rand(100,1000));
 }

 function insert_data($pdo,$data){
     $insert = '';
     $k_string = '';
     foreach($data as $key=>$value){
        $k_string .= $key.",";
        $insert .= (is_string($value) ? "'".$value."'" : $value).",";
     }
     $k_string = trim($k_string,",");
     $insert = trim($insert,",");

     $sql = 'INSERT INTO `order`('.$k_string.') VALUES('.$insert.')';
     
    $pdo->exec($sql);

     return $pdo->lastInsertId();
 }