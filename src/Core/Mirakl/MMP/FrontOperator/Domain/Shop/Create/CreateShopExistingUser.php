<?php

namespace App\Core\Mirakl\MMP\FrontOperator\Domain\Shop\Create;

use Mirakl\MMP\Operator\Domain\Shop\Create\CreateShopExistingUser as MiraklCreateShopExistingUser;

class CreateShopExistingUser extends MiraklCreateShopExistingUser
{
    
    public function getPassword() {
        return $this->getData('password');
    }

    public function setPassword($password) {
         return $this->setData('password', $password);
    }


}
