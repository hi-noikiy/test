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



class Mirasvit_Rma_Helper_Rma_Save_Customer extends Mirasvit_Rma_Helper_Rma_Save_AbstractSave
{

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $customer;

    /**
     * Save function for backend.
     *
     * For arrays description check parent class.
     *
     * @param Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor
     * @param Mage_Customer_Model_Customer $customer
     *
     * @return Mirasvit_Rma_Model_Rma
     *
     * @throws Exception
     */
    public function createOrUpdateRmaCustomer(Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor, $customer)
    {
        $this->customer = $customer;

        return $this->createOrUpdateRma($dataProcessor);
    }

    /**
     * {@inheritdoc}
     */
    protected function setCustomerData($rma) 
    {
        $rma->setCustomerId($this->customer->getId());
        if (trim($rma->getEmail()) == '') {
            $order = $rma->getOrders()->getLastItem();
            if ($order->getCustomerEmail() != '') {
                $rma->setEmail($order->getCustomerEmail());
            } else {
                $rma->setEmail($this->customer->getEmail());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setUserData($rma) 
    {

    }

    /**
     * {@inheritdoc}
     */
    protected function addComment($data, $rma)
    {
        if ($data['comment'] != '') {
            $rma->addComment($data['comment'], false, $rma->getCustomer(), false, false, true, true);
        }
    }
}