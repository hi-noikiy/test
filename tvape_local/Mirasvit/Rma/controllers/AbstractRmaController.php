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
 * Public form for enter to RMA as guest.
 *
 * Class Mirasvit_Rma_AbstractRmaController
 */
abstract class Mirasvit_Rma_AbstractRmaController extends Mage_Core_Controller_Front_Action
{
    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * List guest Rmas.
     * @return void
     */
    public function listAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$this->isGuestLoggedIn() && !$customer->getId()) {
            $this->_redirectUrl(Mage::helper('rma/url')->getGuestRmaUrl());
            return;
        }
        if ($customer->getId()) {
            $this->_redirectUrl(Mage::helper('rma/url')->getRmaListUrl());
            return;
        }
        if ($this->isGuestLoggedIn()) {
            $this->registerGuestData();
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     */
    public function viewAction()
    {
        if ($rma = $this->_initRma()) {
            $this->markAsRead($rma);
            $this->loadLayout();
            $this->_initLayoutMessages('customer/session');
            $this->renderLayout();
        } else {
            $this->_forward('no_rote');
        }
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     */
    protected function markAsRead($rma)
    {
        if ($comment = $rma->getLastComment()) {
            $comment->setIsRead(true)->save();
        }
    }

    /**
     * Save comment.
     */
    public function savecommentAction()
    {
        $session = $this->_getSession();
        if (!($rma = $this->_initRma())) {
            $this->_redirectUrl($this->getRmaListUrl());
            return;
        }
        try {
            $isConfirmShipping = $this->getRequest()->getParam('shipping_confirmation');
            /// we need to confirm shipping BEFORE posting comment
            /// (comment can be from custom variables value in the shipping confirmation dialog)
            if ($isConfirmShipping) {
                $rma->confirmShipping();
                $session->addSuccess(Mage::helper('rma')->__('Shipping is confirmed. Thank you!'));
            }
            Mage::helper('rma/comment_create')->createCommentFromPost($rma, $this->getRequest()->getParams());

            if (!$isConfirmShipping) {
                $session->addSuccess($this->__('Your comment was successfuly added'));
            }
            $this->_redirectUrl($this->getRmaViewUrl($rma));
        } catch (Mage_Core_Exception $e) {
            $session->addError($e->getMessage());
            $this->_redirectUrl($this->getRmaViewUrl($rma));
        }
    }

    /**
     */
    public function printAction()
    {
        if (!$this->_initRma()) {
            $this->_forward('no_rote');
            return;
        }

        $this->loadLayout('print');
        $this->renderLayout();
    }

    /**
     */
    public function printlabelAction()
    {
        if (!$rma = $this->_initRma()) {
            $this->_forward('no_rote');
            return;
        }

        if ($label = $rma->getReturnLabel()) {
            $this->_redirectUrl($label->getUrl());
        } else {
            $this->_forward('no_rote');
        }
    }

    /**
     * @return Mirasvit_Rma_Model_Rma|null
     */
    protected function _initRma()
    {
        if ($id = $this->getRequest()->getParam('guest_id')) {
            $rma = Mage::getModel('rma/rma')->getRmaByGuestId($id);
            if ($rma->getId() > 0) {
                Mage::register('current_rma', $rma);
                return $rma;
            }
        } elseif ($this->_getSession()->isLoggedIn()) {
            $customer = $this->_getSession()->getCustomer();
            if ($id = $this->getRequest()->getParam('id')) {
                $rma = Mage::getModel('rma/rma')->load($id);
                if ($rma->getId() > 0 && $rma->getCustomerId() == $customer->getId()) {
                    Mage::register('current_rma', $rma);
                    return $rma;
                }
            }
        }
        return false;
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    abstract protected function getRmaViewUrl($rma);

    /**
     * @return string
     */
    abstract protected function getRmaListUrl();


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
        return $this->_getSession()->getRmaGuestOrderEmail();
    }

    /**
     * @return bool
     */
    protected function isGuestLoggedIn() {
        return $this->getGuestOrderId() > 0;
    }

    /**
     * Register guest data.
     *
     * @return void
     */
    public function registerGuestData()
    {
        $orderId = $this->getGuestOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::register('current_order', $order);

        $orders = Mage::helper('rma')->getAllowedOrderCollection()
            ->addFieldToFilter('customer_email', $this->getGuestOrderEmail());
        Mage::register('guest_orders', $orders);
    }
}