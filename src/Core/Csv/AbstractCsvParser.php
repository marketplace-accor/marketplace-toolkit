<?php
namespace App\Core\Csv;

use Exception;

abstract class AbstractCsvParser
{
    protected $headers = array();
    protected $csvHeaders = array();
    protected $collection = array();
    protected $errors = array();
        
    /**
     * Parse csv contents
     * 
     * @param type $file
     * @return type
     * @throws Exception
     */
    public function parse($file)
    {
        //Check file exists
        if (!file_exists($file) || !is_readable($file)) {
            throw new Exception('File not found or not readable : '.$file);
        }
        
        //Get headers in files
        $fp = fopen($file, "r");
        if (!$fp) {
            throw new Exception("Could not open file : ".$file);
        }
        
        $csvHeaders = fgetcsv($fp, 0, ";");
                
        //Check headers
        $this->checkHeaders($csvHeaders);
        
        //Read and formatData
        $line = 1;
        while (($row = fgetcsv($fp, 0, ";")) !== false) {
            try {
                $line++;
                $c = 0; 
                $values = array();
                foreach ($csvHeaders as $csvHeader) {
                    $values[$csvHeader] = trim($row[$c]);
                    $c++;
                }
                $this->collection[] = $this->formatRowObject($values);   
                
            } catch (Exception $e) {
                $this->errors[$line] = "An exception has occured parsing line ".$line." : ".$e->getMessage();
            }
        }
        fclose($fp);
        
        return $this->collection;
    }
    
    /**
     * List errors as array
     * 
     * @return type
     */
    public function getErrors()
    {
        return $this->errors;
    }
   
    /**
     * Check each header in csv file
     * 
     * @param type $headers
     * @throws Exception
     */
    protected function checkHeaders($headers)
    {
        $this->initHeaders();
        
        foreach ($headers as $header) {
            if (!in_array($header, $this->headers)) {
                throw new Exception('Header '.$header.' is not allowed');
            }
        }
    }
     
    
    protected abstract function initHeaders();
    
    protected abstract function formatRowObject(array $values);
}

