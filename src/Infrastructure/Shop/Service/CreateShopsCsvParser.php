<?php
namespace App\Infrastructure\Shop\Service;

use Mirakl\MMP\Operator\Domain\Shop\Create\CreateShop;
use Mirakl\MMP\Common\Domain\Collection\AdditionalFieldValueCollection;
use Mirakl\MMP\Common\Domain\AdditionalFieldValue;
use Mirakl\MMP\Common\Domain\Shop\Bank\PaymentInfo;
use Mirakl\MMP\Common\Domain\Shop\ProfessionalInfo;
use App\Core\Mirakl\MMP\FrontOperator\Domain\Shop\Create\CreateShopAddress;
use App\Core\Mirakl\MMP\FrontOperator\Domain\Shop\Create\CreateShopNewUser;
use App\Core\Mirakl\MMP\FrontOperator\Domain\Shop\Create\CreateShopExistingUser;
use App\Core\Csv\AbstractCsvParser;
use Exception;

class CreateShopsCsvParser extends AbstractCsvParser
{
    protected function initHeaders()
    {
        $this->headers = array(
            'civility',
            'firstname',
            'lastname',
            'country',
            'phone',
            'phone_secondary',
            'state',
            'street1',
            'street2',
            'zip_code',
            'city',
            'corporate_name',
            'identification_number',
            'tax_number',
            'user_email',
            'user_exists',
            'password',
            'shop_name',
            'website',
            'suspend',
            'shipping_country',
            'return_policy',
            'is_professional',
            'fax',
            'email',
            'description',
            'currency_iso_code',
            'minimal_order_amount',
            'minimal_order_quantity',
            'shipping_setting_unit',
            'shipping_price_threshold',
            'supplier_group_code'            
        );
    }

    protected function formatRowObject(array $values) {
        
        $shop = new CreateShop();
        
        //Set User
        $this->addUser($shop, $values);
        
        //Set Address
        $this->addAddress($shop, $values);
        
        //Set ProDetails
        $this->addProDetails($shop, $values);
        
        //PaymentInfo (not used for now)
        //$paymentInfo = new PaymentInfo();
        //$shop->setPaymentInfo($paymentInfo);
        
        //Shop Information
        $shop->setShopName(isset($values['shop_name']) ? $values['shop_name'] : null);
        $shop->setWebSite(isset($values['website']) ? $values['website'] : null);
        $shop->setSuspend(isset($values['suspend']) && $values['suspend'] == 1 ? true : false);
        $shop->setShippingCountry(isset($values['shipping_country']) ? $values['shipping_country'] : null);
        $shop->setReturnPolicy(isset($values['return_policy']) ? $values['return_policy'] : null);
        $shop->setIsProfessional(isset($values['is_professional']) && $values['is_professional'] == 0 ? false : true);
        //$shop->setImmunizedUntil(new \DateTime());
        $shop->setFax(isset($values['fax']) ? $values['fax'] : null);
        $shop->setEmail(isset($values['email']) ? $values['email'] : null);
        $shop->setDescription(isset($values['description']) ? $values['description'] : null);
        $shop->setCurrencyIsoCode(isset($values['currency_iso_code']) ? $values['currency_iso_code'] : 'EUR');
        
        //Set AdditionalFields
        $this->addAdditionalFields($shop, $values);
        
        return $shop;
    }

    
    protected function addUser($shop, $values)
    {
        if (!isset($values['user_email']) || !isset($values['user_email'])) {
            throw new Exception('user_email and user_exists are required');
        }
        
        if ($values['user_exists'] == 1) {
            $user = new CreateShopExistingUser($values['user_email']);
            $user->setPassword($values['password']);
            $shop->setExistingUser($user);
        } else {
            $user = new CreateShopNewUser($values['user_email']);
            $user->setPassword($values['password']);
            $shop->setNewUser($user);
        }
    }
    
    protected function addAddress($shop, $values)
    {
        $address = new CreateShopAddress();
        $civility = isset($values['civility']) ? $values['civility'] : null;
        if (!in_array($civility, array('Mr', 'Mrs', 'Miss'))) {
            throw new Exception('Invalid civility : '.$civility.' [Mr, Mrs, Miss]');
        }
        $address->setCivility($civility); 
        
        $address->setFirstname(isset($values['firstname']) ? $values['firstname'] : null);
        $address->setLastname(isset($values['lastname']) ? $values['lastname'] : null);
        $address->setCountry(isset($values['country']) ? $values['country'] : null);
        $address->setPhone(isset($values['phone']) ? $values['phone'] : null);
        $address->setPhoneSecondary(isset($values['phone_secondary']) ? $values['phone_secondary'] : null);
        $address->setState(isset($values['state']) ? $values['state'] : null);
        $address->setStreet1(isset($values['street1']) ? $values['street1'] : null);
        $address->setStreet2(isset($values['street2']) ? $values['street2'] : null);
        $address->setZipCode(isset($values['zip_code']) ? $values['zip_code'] : null);
        $address->setCity(isset($values['city']) ? $values['city'] : null);
        
        $shop->setAddress($address);
    }
    
    
    protected function addProDetails($shop, $values)
    {
        $proDetails = new ProfessionalInfo();
        $proDetails->setCorporateName(isset($values['corporate_name']) ? $values['corporate_name'] : null);
        $proDetails->setIdentificationNumber(isset($values['identification_number']) ? $values['identification_number'] : null);
        $proDetails->setTaxIdentificationNumber(isset($values['tax_number']) ? $values['tax_number'] : null);
        
        $shop->setProDetails($proDetails);
    }
    
    protected function addAdditionalFields($shop, $values)
    {
         $additionalFields = new AdditionalFieldValueCollection();
        if (isset($values['minimal_order_amount']) && trim($values['minimal_order_amount']) != "") {
            $minimalOrderAmount = new AdditionalFieldValue('minimal-order-amount', $values['minimal_order_amount']);
            $additionalFields->add($minimalOrderAmount);
        }
        if (isset($values['minimal_order_quantity']) && trim($values['minimal_order_quantity']) != "") {
            $minimalOrderQuantity = new AdditionalFieldValue('minimal-order-quantity', $values['minimal_order_quantity']);
            $additionalFields->add($minimalOrderQuantity);
        }
        if (isset($values['shipping_setting_unit']) && trim($values['shipping_setting_unit']) != "") {
            if (!in_array($values['shipping_setting_unit'], array('price', 'quantity', 'packaging_unit'))) {
                throw new Exception('Invalid shipping_setting_unit '.$values['shipping_setting_unit'].'  provided [price, quantity, packaging_unit]');
            }
            $shippingSettingUnit = new AdditionalFieldValue('minimal-order-quantity', $values['minimal_order_quantity']);
            $additionalFields->add($shippingSettingUnit);
        }
        if (isset($values['shipping_price_threshold']) && trim($values['shipping_price_threshold']) != "") {
            $shippingPriceThreshold = new AdditionalFieldValue('shipping-price-threshold', $values['shipping_price_threshold']);
            $additionalFields->add($shippingPriceThreshold);
        }
        if (isset($values['supplier_group_code']) && trim($values['supplier_group_code']) != "") {
            $supplierGroupCode = new AdditionalFieldValue('supplier-group-code', $values['supplier_group_code']);
            $additionalFields->add($supplierGroupCode);
        }
        
        if ($additionalFields->count() > 0) {
            $shop->setShopAdditionalFields($additionalFields);
        }
    }
}

