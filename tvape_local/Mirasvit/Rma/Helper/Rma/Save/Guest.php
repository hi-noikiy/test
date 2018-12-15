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



class Mirasvit_Rma_Helper_Rma_Save_Guest extends Mirasvit_Rma_Helper_Rma_Save_AbstractSave
{

    /**
     * @var Mage_Sales_Model_Order
     */
    protected $order;

    /**
     * Save function for frontend.
     *
     * For arrays description check parent class.
     *
     * @param Mirasvit_Rma_Helper_Rma_Save_PostDataProcessor $dataProcessor
     * @param Mage_Sales_Model_Order $order
     *
     * @return Mirasvit_Rma_Model_Rma
     *
     * @throws Exception
     */
    public function createOrUpdateRmaGuest($dataProcessor, $order)
    {
        $this->order = $order;

        return $this->createOrUpdateRma($dataProcessor);
    }

    /**
     * {@inheritdoc}
     */
    protected function setCustomerData($rma) 
    {
        if ($guestData = Mage::getSingleton('customer/session')->getRmaGuestOfflineData()) {
            $rma->setEmail($guestData['email']);
            $rma->setFirstname($guestData['firstname']);
            $rma->setLastname($guestData['lastname']);
            Mage::getSingleton('customer/session')->unsRmaGuestOfflineData();
        } else {
            if ($this->order->getCustomerId()) { //if customer is registered, but was not logged in
                $rma->setCustomerId($this->order->getCustomerId());
            }
            $rma->setEmail($this->order->getCustomerEmail());
            $rma->setOrderId($this->order->getid());
        }
    }

    /**
     * @Overwrite
     *
     * {@inheritdoc}
     */
    protected function setRmaAddress($rma)
    {
        $address = $this->order->getShippingAddress();
        if ($address) {
            $this->setRmaAddressData($rma, $address);
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