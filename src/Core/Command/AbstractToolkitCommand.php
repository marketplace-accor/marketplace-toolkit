<?php 
namespace App\Core\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Mirakl\MMP\Operator\Client\OperatorApiClient;

abstract class AbstractToolkitCommand extends ContainerAwareCommand
{
    protected $apiClient;
    protected $input;
    protected $output;
    
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        //Initialize Api Client
        $this->apiClient = new OperatorApiClient($this->getContainer()->getParameter('mirakl.api_url'), $this->getContainer()->getParameter('mirakl.api_key'));

        
    }

    protected function writeln($messages)
    {
       $this->output->writeln($messages);
    }
}