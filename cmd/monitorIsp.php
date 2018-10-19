<?php

namespace cmd;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use admin\helper\consumer\monitorIsp as Isp;


/**
 * 
 */
class monitorIsp extends Command
{
    public function configure()
    {
        $this->setName('monitor:isp');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
         (new Isp())->exec();
    }
}
