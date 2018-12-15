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



/**
 * Class Mirasvit_Rma_Test_Helper_RmaPHPUnit.
 */
class Mirasvit_Rma_Test_Helper_RmaPHPUnit extends EcomDev_PHPUnit_Test_Case
{
    /**
     * @var Mirasvit_Rma_Helper_Process
     */
    protected $helper;
    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $customer;
    /**
     * @var string
     */
    protected $fixtureFolder;
    /**
     * @var int
     */
    protected $userId;

    protected function setUp()
    {
        $this->helper = Mage::helper('rma/rma_save_postDataProcessor');
        $this->fixtureFolder = __DIR__.'/RmaFixture/';
        $this->user = $this->getMockUser();
        $this->customer = null;

        $this->mockConfigMethod(array(
            'getGeneralIsOfflineOrdersAllowed' => 1,
        ));
        if (!Mage::registry('isSecureArea')) {
            Mage::register('isSecureArea', true);
        }

        $this->createOrLoadCustomer();

        $fixture = 'Mirasvit_Rma_Test_Helper_OfflineRmaTest';
        EcomDev_PHPUnit_Test_Case_Util::getFixture($fixture)
            ->setScope(EcomDev_PHPUnit_Model_Fixture_Interface::SCOPE_SHARED)
            ->loadForClass($fixture);
    }

    protected function mockConfigMethod($methods)
    {
        $config = $this->getModelMock('rma/config', array_keys($methods));
        foreach ($methods as $method => $value) {
            $config->expects($this->any())
            ->method($method)
            ->will($this->returnValue($value));
        }
        $this->replaceByMock('singleton', 'rma/config', $config);
    }

    protected function getMockUser()
    {
        $userMock = $this->getModelMock(
            'admin/user',
            array('login', 'getId', 'save', 'authenticate', 'getRole')
        );

        $userMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));

        return $userMock;
    }

    protected function createOrLoadCustomer()
    {
        $data = include $this->fixtureFolder.'customer.php';

        /** @var Mage_Customer_Model_Resource_Customer_Collection $customers */
        $customers = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('email', $data['email']);

        if ($customers->count()) {
            $this->customer = $customers->getFirstItem();
        } else {
            $this->customer = $this->createCustomer($data);
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function applyCustomerToData($data)
    {
        $data['customer_id'] = $this->customer->getId();

        return $data;
    }

    /**
     * @param array $data
     *
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
}
