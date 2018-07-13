<?php
namespace App\Core\Mirakl\MMP\FrontOperator\Domain\Shop\Create;

use Mirakl\MMP\FrontOperator\Domain\Shop\Create\CreateShopAddress as BaseCreateShopAddress;

class CreateShopAddress extends BaseCreateShopAddress
{
    
    public function getStreet1() {
       return $this->getData('street1');
    }
   
    public function setStreet1(string $street1) {
       return $this->setData('street1', $street1);
    }
    
    public function unsetStreet1()
    {
        return $this->unsetData('street1');
    }
    
    public function hasStreet1()
    {
        return $this->hasData('street1');
    }
    
    public function getStreet2() {
       return $this->getData('street2');
    }
   
    public function setStreet2(string $street2) {
       return $this->setData('street2', $street2);
    }
    
    public function unsetStreet2()
    {
        return $this->unsetData('street2');
    }
    
    public function hasStreet2()
    {
        return $this->hasData('street2');
    }
    

}