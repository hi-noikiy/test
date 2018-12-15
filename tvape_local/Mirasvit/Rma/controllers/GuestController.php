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


require_once Mage::getModuleDir('controllers', 'Mirasvit_Rma').DS.'AbstractRmaController.php';

/**
 * Public form for enter to RMA as guest.
 *
 * Class Mirasvit_Rma_Rma_GuestController
 */
class Mirasvit_Rma_GuestController extends Mirasvit_Rma_AbstractRmaController
{
    /**
     * Post action. Checks for correct email/order
     * @return void
     */
    public function guestAction()
    {
        $session = $this->_getSession();
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if ($customer->getId()) {
            $this->_redirectUrl(Mage::helper('rma/url')->getNewRmaUrl());
            return;
        }
        try {
            $order = $this->_initOrder();
            if ($order) {
                $this->_getSession()->setRmaGuestOrderId($order->getId());
                $this->_getSession()->setRmaGuestEmail($order->getCustomerEmail());
                $this->_redirectUrl(Mage::helper('rma/url')->getGuestRmaListUrl());

                return;
            } elseif (Mage::app()->getRequest()->getParam('order_increment_id')) {
                $store = Mage::app()->getStore();
                if (Mage::getSingleton('rma/config')->getPolicyAllowGuestOfflineRMA($store)) {
                    $this->_getSession()->setRmaGuestEmail(Mage::app()->getRequest()->getParam('email'));
                    $this->_redirectUrl(Mage::helper('rma/url')->getGuestOfflineRmaUrl());
                } else {
                    throw new Mage_Core_Exception(Mage::helper('rma')->__('Wrong Order #, Email or Last Name'));
                }
            }
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
        }

        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * @return void
     */
    public function offlineAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Frontend function to download FedEx labels
     *
     * @return void
     * @throws Zend_Controller_Response_Exception
     */
    public function getFedExLabelAction()
    {
        $label = Mage::getModel('rma/fedex_label')->load($this->getRequest()->getParam('label_id'));
        if ($label) {
            $this->getResponse()->clearHeaders();
            $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Content-Disposition', 'attachment; filename=fedexlabel_'.$label->getTrackNumber().'.pdf')
                ->setHeader('Content-type', 'application/x-pdf');
            $this->getResponse()->sendHeaders();
            $this->getResponse()->clearBody();
            echo $label->getLabelBody();
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    protected function getRmaViewUrl($rma)
    {
        return Mage::helper('rma/url')->getGuestRmaViewUrl($rma);
    }

    /**
     * @return string
     */
    protected function getRmaListUrl()
    {
        return Mage::helper('rma/url')->getGuestRmaListUrl();
    }

    /**
     * @return false|Mage_Sales_Model_Order
     */
    protected function _initOrder()
    {
        if (($orderId = Mage::app()->getRequest()->getParam('order_increment_id')) &&
            ($email = Mage::app()->getRequest()->getParam('email'))) {
            $orderId = trim($orderId);
            $orderId = str_replace('#', '', $orderId);
            $collection = Mage::getModel('sales/order')->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('increment_id', $orderId);
            if ($collection->count()) {
                $order = $collection->getFirstItem();
                $email = trim(strtolower($email));
                if ($email != strtolower($order->getCustomerEmail())
                    && $email != strtolower($order->getCustomerLastname())) {
                    return false;
                }

                return $order;
            }
        }
    }
}
