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
use PhpAmqpLib\Wire\AMQPTable;

class RabbitMQ
{
    /**
     * @param string $queue
     * @param string $exchange
     * @param string $messageBody
     * @param array $arguments 附加参数['x-max-priority'=>{int}]
     * @param int $priority
     */
    public static function push($queue = 'test',$exchange = 'test', $messageBody = 'ok',$arguments = [], $priority = 1)
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));
        $chanel = $connection->channel();
        $args = new AMQPTable($arguments);
        $chanel->queue_declare($queue, false, true, false, false, false, $args);
        $chanel->exchange_declare($exchange, 'fanout', false, true, false);
        $chanel->queue_bind($queue, $exchange);
        $message = new AMQPMessage($messageBody, [
            'content_type' => 'text/plain',
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_NON_PERSISTENT,
            'priority' => $priority
        ]);
        $chanel->basic_publish($message, $exchange);
        $chanel->close();
        $connection->close();
    }


    /**
     * @param string $queue
     * @param array $arguments
     * @return bool|mixed
     */
    public static function read($queue = 'test', $arguments = [])
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));
        $channel = $connection->channel();
        $args = new AMQPTable($arguments);
        $channel->queue_declare($queue, false, true, false, false, false, $args);
        $message = $channel->basic_get($queue);
        //$channel->basic_ack($message->delivery_info['delivery_tag']);
        $channel->close();
        $connection->close();
        if (isset($message->body)) {
            return $message;
        }else{
            return false;
        }


    }

    /**
     * @param string $queue
     * @param array $arguments
     */
    public static function consumer($queue = 'test', $arguments = [])
    {
        $connection = new AMQPStreamConnection(env('RABBITMQ_HOST'), env('RABBITMQ_PORT'), env('RABBITMQ_USER'), env('RABBITMQ_PASSWORD'));
        $channel = $connection->channel();
        $args = new AMQPTable($arguments);
        $channel->queue_declare($queue, false, true, false, false, false, $args);
        $message = $channel->basic_get($queue);
        $channel->basic_ack($message->delivery_info['delivery_tag']);
        $channel->close();
        $connection->close();
    }
}