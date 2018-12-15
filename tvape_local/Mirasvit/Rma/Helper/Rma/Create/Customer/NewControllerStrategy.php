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



class Mirasvit_Rma_Helper_Rma_Create_Customer_NewControllerStrategy extends Mirasvit_Rma_Helper_Rma_Create_AbstractNewControllerStrategy
{
    /**
     * {@inheritdoc}
     */
    public function preDispatch() {

    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateRma($data) {
        $dataProcessor = Mage::helper('rma/rma_save_postDataProcessor');
        $dataProcessor->setData($data);
        $customer = $this->_getSession()->getCustomer();
        $rma = Mage::helper('rma/rma_save_customer')->createOrUpdateRmaCustomer(
            $dataProcessor,
            $customer
        );
        return $rma;
    }

    /**
     * {@inheritdoc}
     */
    public function initRma($data)
    {
        if (isset($data['id'])) {
            $id = (int)$data['id'];
            $rma = Mage::getModel('rma/rma')->load($id);
            $customer = $this->_getSession()->getCustomer();
            if ($rma->getCustomerId() != $customer->getId()) {
                throw new Mage_Core_Exception('Incorrect RMA');
            }

            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);
                return $rma;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setLayout($layout)
    {
        $layout->getUpdate()
            ->addHandle('default')
            ->addHandle('customer_account');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrders()
    {
        return Mage::helper('rma')->getAllowedOrderCollection($this->getCustomer());
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer() {
        return $this->_getSession()->getCustomer();
    }
}