<?php

namespace cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use admin\helper\consumer\syncerpstatus;


/**
 * 
 */
class SyncErpStatusCmd extends Command
{
    public function __construct($msg = '')
    {
        $this->msg = $msg;
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('sync:erpstatus');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    	## 前期 使用 定时pull，后期 被动接口 触发执行
        $res = (new syncerpstatus())->exec();
        if($res){
            echo 'execute finished!';exit();
        }else{
            echo 'some mistakes happend while executing!';exit();
        }
    }

}
