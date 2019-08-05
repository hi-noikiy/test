<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Model\Payment;

class Information extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Paysafe\Paysafe\Model\ResourceModel\Payment\Information');
    }

    /**
     * get payments information
     * @param  array $parameters
     * @return object
     */
    public function getPaymentInformation($parameters)
    {
        $informationCollection = $this->getCollection()
            ->addFieldToFilter('customer_id', $parameters['customerId']);

        return $informationCollection->getData();
    }

    /**
     * insert a payment account into the database
     * @param  array $parameters
     * @return void
     */
    public function insertRegistration($parameters)
    {   
        $brand = $this->getCardBrand($parameters['cards'][0]['cardType']);
        $this->setData ('customer_id', $parameters['customerId']);
        $this->setData ('merchant_customer_id', $parameters['merchantCustomerId']);
        $this->setData ('environment', $parameters['environment']);
        $this->setData ('profile_id', $parameters['id']);
        $this->setData ('card_id', $parameters['cards'][0]['id']);
        $this->setData ('brand', $brand);
        if (isset($parameters['cards'][0]['holderName'])) {
            $this->setData ('holder', $parameters['cards'][0]['holderName']);    
        }
        $this->setData ('email', $parameters['email']);
        $this->setData ('last_digits', $parameters['cards'][0]['lastDigits']);
        $this->setData ('expiry_month', $parameters['cards'][0]['cardExpiry']['month']);
        $this->setData ('expiry_year', $parameters['cards'][0]['cardExpiry']['year']);
        $this->setData ('payment_token', $parameters['cards'][0]['paymentToken']);
        $this->save();
    }

    /**
     * update a payment account into the database
     * @param  array $parameters
     * @param  string $informationId
     * @return void
     */
    public function updateRegistration($parameters, $informationId)
    {
        $brand = $this->getCardBrand($parameters['cards'][0]['cardType']);
        if (isset($parameters['cards'][0]['holderName'])) {
            $cardHolderName = $parameters['cards'][0]['holderName'];    
        }else{
            $cardHolderName = '';
        }
        $this->load($informationId)
            ->setData ('customer_id', $parameters['customerId'])
            ->setData ('merchant_customer_id', $parameters['merchantCustomerId'])
            ->setData ('environment', $parameters['environment'])
            ->setData ('profile_id', $parameters['id'])
            ->setData ('card_id', $parameters['cards'][0]['id'])
            ->setData ('brand', $brand)
            ->setData ('holder', $cardHolderName)
            ->setData ('email', $parameters['email'])
            ->setData ('last_digits', $parameters['cards'][0]['lastDigits'])
            ->setData ('expiry_month', $parameters['cards'][0]['cardExpiry']['month'])
            ->setData ('expiry_year', $parameters['cards'][0]['cardExpiry']['year'])
            ->setData ('payment_token', $parameters['cards'][0]['paymentToken'])
            ->save();
    }

    /**
     * delete a payment account registered based on id
     * @param  string $informationId
     * @return void
     */
    public function deletePaymentInformationById($informationId)
    {
        $this->load($informationId)->delete();
    }

    /**
     * get credit card brand
     * @param  string $code
     * @return string
     */
    protected function getCardBrand($code)
    {
        switch ($code) {
            case 'AM':
                $brand = 'AMERICANEXPRESS';
                break;
            case 'DC':
                $brand = 'DINERS';
                break;
            case 'JCB':
                $brand = 'JCB';
                break;
            case 'MD':
                $brand = 'MAESTRO';
                break;
            case 'MC':
                $brand = 'MASTERCARD';
                break;
            case 'VI':
            case 'VD':
            case 'VE':
                $brand = 'VISA';
                break;
        }
        return $brand;
    }

    /**
     * get the payment account registered based on the information id
     * @param  array $parameters
     * @param  int $informationId
     * @return object
     */
    public function getRegistrationByInformationId($parameters, $informationId)
    {
        $informationCollection = $this->getCollection()
            ->addFieldToFilter('information_id', (int)$informationId)
            ->addFieldToFilter('customer_id', $parameters['customerId'])
            ->setPageSize(1);

        return $informationCollection->getData();
    }
}
