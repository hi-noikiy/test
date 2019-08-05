<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Model\Customer;

class Customer
{
    protected $customerSession;
    protected $checkoutSession;
    protected $accountManagement;
    protected $region;
    protected $customer;
    protected $maleGender = 1;
    protected $femaleGender = 2;
    protected $femaleCustomerGender = 'F';
    protected $maleCustomerGender = 'M';
    protected $helperCore;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Directory\Model\Region $region,
        \Paysafe\Paysafe\Helper\Core $helperCore
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->customer = $customerSession->getCustomer();
        $this->accountManagement = $accountManagement;
        $this->region = $region;
        $this->helperCore = $helperCore;
    }

    /**
     * get a customer session
     * @return object
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * get id
     * @return object
     */
    public function getId()
    {
        return $this->customer->getId();
    }

    /**
     * check if a customer already login
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * get a gender
     * @return boolean|string
     */
    public function getGender()
    {
        if ($this->isLoggedIn()) {
            if ($this->customer->getGender() == $this->maleGender) {
                return $this->maleCustomerGender;
            } elseif ($this->customer->getGender() == $this->femaleGender) {
                return $this->femaleCustomerGender;
            }
        } else {
            if ($this->checkoutSession->getQuote()->getCustomerGender() == $this->maleGender) {
                return $this->maleCustomerGender;
            } elseif ($this->checkoutSession->getQuote()->getCustomerGender() == $this->femaleGender) {
                return $this->femaleCustomerGender;
            }
        }

        return false;
    }

    /**
     * get a date of birth
     * @return date
     */
    public function getDob()
    {
        if ($this->isLoggedIn()) {
    	   return $this->customer->getDob();
        }else {
           return $this->checkoutSession->getQuote()->getCustomerDob();
        }
    }

    /**
     * get a customer information
     * @return array
     */
    public function getCustomerInformation()
    {
        $customerInformation = array();
        $customerInformation['merchantCustomerId'] = time() . str_replace(' ', '', $this->helperCore->getGeneralCredentials()['merchant_name']) . $this->getId();
        $customerInformation['email'] = $this->customer->getEmail();
        $customerInformation['firstName'] = $this->customer->getFirstname();
        $customerInformation['lastName'] = $this->customer->getLastname();

        if ($this->getGender()) {
            $customerInformation['gender'] = $this->getGender();
        }

        return $customerInformation;
    }

}
