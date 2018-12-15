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



class Mirasvit_Rma_Helper_Rma_Create_Guest_NewControllerStrategy extends Mirasvit_Rma_Helper_Rma_Create_AbstractNewControllerStrategy
{
    /**
     * {@inheritdoc}
     */
    public function preDispatch() {
        $this->registerGuestData();
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdateRma($data) {
        $dataProcessor = Mage::helper('rma/rma_save_postDataProcessor');
        $dataProcessor->setData($data);
        $rma = Mage::helper('rma/rma_save_guest')->createOrUpdateRmaGuest(
            $dataProcessor,
            Mage::registry('current_order')
        );
        return $rma;
    }

    /**
     * @return int
     */
    protected function getGuestOrderId() {
        return $this->_getSession()->getRmaGuestOrderId();
    }

    /**
     * @return string
     */
    protected function getGuestOrderEmail() {
        return $this->_getSession()->getRmaGuestEmail();
    }

    /**
     * @return bool
     */
    protected function isGuestLoggedIn() {
        return $this->getGuestOrderId() > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function initRma($data)
    {
        if (isset($data['id'])) {
            $id = (int)$data['id'];
            $rma = Mage::getModel('rma/rma')->load($id);
            if ($rma->getEmail() != $this->getGuestOrderEmail()) {
                throw new Mage_Core_Exception('Incorrect Guest RMA');
            }

            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);
                return $rma;
            }
        }
    }

    /**
     * Register guest data.
     *
     * @return void
     */
    protected function registerGuestData()
    {
        $orderId = $this->getGuestOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::register('current_order', $order);

        $orders = Mage::helper('rma')->getAllowedOrderCollection()
            ->addFieldToFilter('customer_email', $this->getGuestOrderEmail());
        Mage::register('guest_orders', $orders);
    }


    /**
     * {@inheritdoc}
     */
    public function setLayout($layout)
    {
        $layout->getUpdate()
            ->addHandle('default')
            ->addHandle('page_two_columns_right');
    }

    /**
     * {@inheritdoc}
     */
    public function getAllowedOrders()
    {
        return Mage::registry('guest_orders');
    }
}