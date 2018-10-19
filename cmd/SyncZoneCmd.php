<?php

namespace cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use admin\helper\consumer\synczone;


/**
 * 
 */
class SyncZoneCmd extends Command
{
    public function __construct($msg = '')
    {
        $this->msg = $msg;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('sync:zone');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    	# jade 后期rabbitmq服务器连接 应当写在 配置文件
    	$exchange = 'erp.pc.Exchange';
		$queue = 'qr_site_zone';
		$consumerTag = 'consume the zone queue';
		$config = ['host'=>'192.168.105.212','port'=>'5672','user'=>'queue','pass'=>'1234','vhost'=>'/'];
		$connection = new AMQPStreamConnection($config['host'], $config['port'], $config['user'], $config['pass'], $config['vhost']);
		$channel = $connection->channel();
		$channel->queue_declare($queue, false, true, false, false);
		$channel->exchange_declare($exchange, 'direct', false, true, false);
		$channel->queue_bind($queue, $exchange);
		$channel->basic_consume($queue, $consumerTag, false, false, false, false, function($message = ''){
		    $zoneInfo = json_decode($message->body,1);
		    $res = (new synczone())->exec($zoneInfo);
		    if($res){
		    	$message->delivery_info['channel']->basic_ack($message->delivery_info['delivery_tag']);
		    	if ($message->body === 'quit') {
			        $message->delivery_info['channel']->basic_cancel($message->delivery_info['consumer_tag']);
			    }
		    }
		});
		if(count($channel->callbacks) < 1){
			$channel->close();
	    	$connection->close();
	    	exit('all zone queue have been consumed!');
		}
		register_shutdown_function(function($channel, $connection){
			$channel->close();
	    	$connection->close();
	    	exit('zone queue have been consumed!');
		}, $channel, $connection);
		while (count($channel->callbacks)) {
		    $read = array($connection->getSocket());
			$write = null;
		    $except = null;
		    try {
		    	if (false === ($changeStreamsCount = stream_select($read, $write, $except, 0))) {
			        echo 'No stream data were passedall,zone queue have been consumed!';exit();
			    } elseif ($changeStreamsCount > 0) {
			    	// error_log(print_r($changeStreamsCount,1),3,'csc.txt');
			        $channel->wait();
			    } elseif ($changeStreamsCount == 0) {
			    	echo 'No stream data received! ';exit();
			    }
		    } catch (Exception $e) {
		    	echo 'exception happened please check the config about RabbitMQ';exit();
// 		    	error_log(print_r($e,1).'--zone队列异常.'.date('Y-m-d H:i:s').'
// ',3,'queue_zone_error.txt');
		    }
		}
    }
}
