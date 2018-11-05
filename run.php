<?php

require './pdo.php';
require './redis.php';

echo "->开始监听队列\n";

//实时监听
while(1){
    $list_length = $redis->llen('seckill_list');
    echo "============ ".date('Y-m-d H:i:s',time())." =============\n";
    echo "->处理队列数量:".$list_length."\n";
    if($list_length <= 0){
        echo "Done:当前没有可处理的订单!\n";
    }
    $orderinfo = $redis->rpop('seckill_list');
    if(!empty($orderinfo)){
        $orderinfo = explode('orderid_',$orderinfo);
        $order_id = $orderinfo[1];
        if(!empty($order_id)){
            $sql = "SELECT * FROM `order` WHERE `id` = :id";
            $data = [];
            try{
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':id',$order_id);
                $stmt->execute();
                $data = $stmt->fetch();
            }catch(Exception $e){
                echo "Error:".$e->getMessage()."\n";
                continue;
            }

            if(empty($data)){
                echo "Error:订单(".$order_id.")获取失败!\n";
                continue;
            }

            if($data['queue'] == 1){
                echo "Done:订单('.$order_id.')已经被处理!\n";
                continue;
            }
            $submit = [];
            $submit['queue'] = 1;
            $rs = update_data($pdo,$order_id,$submit);

            if($rs === FALSE){
                echo "Error:订单更新失败!\n";
                put_in($redis,$order_id);
                continue;
            }

            echo "->订单(".$order_id.")已经处理!\n";
            
        }
        
    }
    sleep(1);
}

function put_in($redis,$order_id){
    return $redis->lpush('seckill_list',"orderid_".$order_id);
}


function update_data($pdo,$id,$data){
    $update = '';

    foreach($data as $key=>$value){
       $update .= "`".$key."`=".(is_string($value) ? "'".$value."'" : $value).",";
    }

    $update = trim($update,",");

    $sql = 'UPDATE `order` SET '.$update.' WHERE id = '.$id;
    
    return $pdo->exec($sql);

}