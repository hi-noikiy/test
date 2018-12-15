<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/extension_rma
 * @version   2.4.7
 * @copyright Copyright (C) 2017 Mirasvit (https://mirasvit.com/)
 */



class Mirasvit_Rma_Helper_Rma_Save_CustomerFactory
{
    /**
     * @param array $data
     * @return array
     */
    public function loadOrCreate($data)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->setWebsiteId($data['website_id']);
        $customer->loadByEmail($data['email']);
        if ($customer->getId()) {
            $this->_getSession()->addSuccess(
                Mage::helper('adminhtml')->__(
                    'Account for email "%s" is already exists so we created RMA for this account',
                    $customer->getEmail(),
                    $customer->getEmail()
                )
            );
            return $customer;
        }

        return $this->createCustomer($data);
    }

    /**
     * @param array $data
     * @return bool|Mage_Customer_Model_Customer
     *
     * @throws Mage_Core_Exception
     */
    protected function createCustomer($data)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer');

        /** @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form');
        $customerForm->setEntity($customer)
            ->setFormCode('adminhtml_customer')
            ->ignoreInvisible(false)
        ;

        $formData = $data;
        $errors = $customerForm->validateData($formData);

        if ($errors !== true) {
            foreach ($errors as $error) {
                $this->_getSession()->addError($error);
            }
            return false;
        }

        $customerForm->compactData($formData);
        $customer->setPassword($customer->generatePassword());
        $customer->save();
        $storeId = $customer->getSendemailStoreId();
        $customer->sendNewAccountEmail('registered', '', $storeId);

        return $customer;
    }

    /**
     * Retrieve adminhtml session model object
     *
     * @return Mage_Adminhtml_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }
}