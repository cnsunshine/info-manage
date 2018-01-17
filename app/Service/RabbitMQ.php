<?php
/**
 * Created by PhpStorm.
 * User: sunshine
 * Date: 18-1-17
 * Time: 下午12:57
 */

namespace App\Service;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
    public static function push($queue = 'test',$exchange = 'test', $messageBody = 'ok')
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));
        $chanel = $connection->channel();
        $chanel->queue_declare($queue, false, true, false, false);
        $chanel->exchange_declare($exchange, 'fanout', false, true, false);
        $chanel->queue_bind($queue, $exchange);
        $message = new AMQPMessage($messageBody, [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT
        ]);
        $chanel->basic_publish($message, $exchange);
        $chanel->close();
        $connection->close();
    }


    public static function read($queue = 'test')
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $message = $channel->basic_get($queue);
        //$channel->basic_ack($message->delivery_info['delivery_tag']);
        $channel->close();
        $connection->close();
        if ($message->body) {
            return $message;
        }else{
            return false;
        }


    }

    public static function consumer($queue = 'test')
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));
        $channel = $connection->channel();
        $channel->queue_declare($queue, false, true, false, false);
        $message = $channel->basic_get($queue);
        $channel->basic_ack($message->delivery_info['delivery_tag']);
        $channel->close();
        $connection->close();
    }
}