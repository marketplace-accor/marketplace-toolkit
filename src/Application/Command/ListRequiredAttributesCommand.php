<?php 
namespace App\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Mirakl\MCI\Operator\Request\Attribute\GetAttributesRequest;
use Mirakl\MCI\Common\Domain\Collection\AttributeCollection;
use Mirakl\MCI\Common\Domain\Attribute;
use App\Core\Command\AbstractToolkitCommand;
use Exception;

class ListRequiredAttributesCommand extends AbstractToolkitCommand
{
    
    protected $categories = array();
    protected $required_on_all_categories = array();
    
    protected function configure()
    {
        $this->setName('app:list-required-attributes');
        $this->setDescription('Creates a CSV file listing required attributes by category code');
        
    }

    /**
     * Create shops parsing CSV file
     * 
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        
        $this->writeln('Starting process...');
        
        try {
            
            $request = new GetAttributesRequest();
            $results = $request->run($this->apiClient);
            if (!$results instanceOf AttributeCollection) {
                throw new Exception('Instance of AttributeCollection expected for results. Got : '.get_class($results));
            }
            
            $this->writeln('Filtering attributes process...');
            
            foreach ($results as  $attribute) {
                if (!$attribute instanceOf Attribute) {
                    throw new Exception('Instance of Attribute expected for result element. Got : '.get_class($attribute));
                }
                
                if ($attribute->isRequired()) {
                    
                    if (strlen($attribute->getHierarchyCode()) > 0) {
                        
                        if (isset($this->categories[$attribute->getHierarchyCode()])) {
                            $this->categories[$attribute->getHierarchyCode()][] = $attribute->getCode();
                        } else {
                            $this->categories[$attribute->getHierarchyCode()] = array($attribute->getCode());
                        }
                        
                    } else {
                        $this->required_on_all_categories[] = $attribute->getCode();
                    }
                }
            }
           
            $this->writeln('Writing report file...');
            $file = $this->getContainer()->getParameter('kernel.project_dir'). DIRECTORY_SEPARATOR. '..'.DIRECTORY_SEPARATOR.'required_attributes_by_categories.csv';
            $fp = fopen($file, "w+");
            fputcsv ($fp, array('category_code', 'attibutes_codes'), ';');
            foreach ($this->categories as $categoryCode => $requiredAttributesCodes) {
                fputcsv($fp, array($categoryCode, implode(",",$this->required_on_all_categories).",".implode(',',$requiredAttributesCodes)), ";");
            }
            fclose($fp);
           
           
        } catch (Exception $e) {
            $this->writeln("An exeception has occured : ".$e->getMessage());
            
        }
        $this->writeln('Done!');
    }
    
}