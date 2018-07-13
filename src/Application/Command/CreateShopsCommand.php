<?php 
namespace App\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Mirakl\MMP\Operator\Request\Shop\CreateShopsRequest;
use Mirakl\MMP\Operator\Domain\Shop\Create\CreateShop;
use Mirakl\MMP\Operator\Domain\Collection\Shop\Create\CreateShopCollection;
use Mirakl\MMP\FrontOperator\Domain\Shop\Create\CreatedShops;
use App\Core\Command\AbstractToolkitCommand;
use App\Infrastructure\Shop\Service\CreateShopsCsvParser;
use Exception;

class CreateShopsCommand extends AbstractToolkitCommand
{
    
    protected function configure()
    {
        $this->setName('app:create-shops');
        $this->addArgument('csv', InputArgument::REQUIRED, 'Shops to create CSV-file');
        $this->setDescription('Create shops parsing CSV file');
        
    }

    /**
     * Create shops parsing CSV file
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $this->writeln('Starting create shops command');
        
        try {
            //Parse csv file and build CreateShopsCollection
            $csv = $this->getContainer()->getParameter('kernel.project_dir'). DIRECTORY_SEPARATOR. '..'.DIRECTORY_SEPARATOR.$input->getArgument('csv');
            $shopsToCreate = $this->getShopsFromCsv($csv);

            foreach ($shopsToCreate as $shopToCreate) {
                $this->createShop($shopToCreate);
            }
        } catch (Exception $e) {
            $this->writeln("An exeception has occured : ".$e->getMessage());
            
        }
        $this->writeln('Done!');
    }
    
    
    /**
     * Create shop
     * 
     * @param CreateShop $shopToCreate
     * @throws Exception
     */
    protected function createShop(CreateShop $shopToCreate)
    {
        try {
            $this->writeln("Creating shop ".$shopToCreate->getShopName());
            
            $shop = new CreateShopCollection();
            $shop->add($shopToCreate);
            $request = new CreateShopsRequest($shop);
            
            $result = $request->run($this->apiClient);
            
            if (!$result instanceOf CreatedShops) {
                throw new Exception('Result should be instance of CreatedShops');
            }
            
            $shopResult = $result->getShopReturns()->first();
            
            if (!is_null($shopResult->getShopError())) {
                var_export($shopResult->getShopError()->getErrors());
                throw new Exception('Errors found!');
                
            } else if (!is_null($shopResult->getShopCreated())) {
                $this->writeln("Shop ".$shopToCreate->getShopName()." created successfully!");
            }
        } catch (Exception $e) {
            $this->writeln('An error occured while creating shop '.$shopToCreate->getShopName().'  : '.$e->getMessage());
        }
    }
    
    /**
     * Parse CSV file
     * 
     * @param type $csv
     * @return type
     */
    protected function getShopsFromCsv($csv)
    {
        $this->writeln('Parsing file : '.$csv);
        $csvParser = new CreateShopsCsvParser();
        $shopsToCreate = $csvParser->parse($csv);
        
        if (count($csvParser->getErrors()) > 0) {
            $this->writeln('Warning : errors occured while parsing file');
            foreach ($csvParser->getErrors() as $line => $errorMessage) {
                $this->writeln('On line ['.$line.'] : '.$errorMessage);
            }
        }
        
        $this->writeln(count($shopsToCreate).' shops to create');
        
        return $shopsToCreate;
    }
}