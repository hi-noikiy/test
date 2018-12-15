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



class Mirasvit_Rma_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Observes event core_block_abstract_to_html_after and removes standard address block from header.
     * This is need in RMA Print Template to prevent return address ambiguity.
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Varien_Event_Observer
     */
    public function afterOutput($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $transport = $observer->getEvent()->getTransport();
        if (!isset($transport)) {
            return $this;
        }
        if (get_class($block) != 'Mage_Page_Block_Html') {
            return $this;
        }

        $html = $transport->getHtml();
        if (strpos($html, 'print-head') && $block->getChild('content')) {
            if (!$block->getChild('content')->getChild('rma.rma.print')) {
                return $this;
            }
            $blockBegin = strpos($html, '<div class="print-head">');
            $blockEnd = strpos($html, '</div>', $blockBegin) + 7;
            $headBlock = substr($html, $blockBegin, $blockEnd - $blockBegin);
            if (strpos($headBlock, '<address>')) {
                $addrBegin = strpos($headBlock, '<address>') - 1;
                $addrEnd = strpos($headBlock, '</address>') + 10;
                $headBlock = substr($headBlock, 0, $addrBegin).substr($headBlock, $addrEnd);
            }
            $ourHtml = substr($html, 0, $blockBegin).$headBlock.substr($html, $blockEnd);
            $transport->setHtml($ourHtml);
        }

        return $this;
    }

    /**
     * We have changed URL from /rma/ to /returns/.
     * So we need to do redirect.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onControllerActionPredispatch($observer)
    {
        if (Mage::helper('mstcore/version')->getEdition() == 'ee') {
            return;
        }
        $url = Mage::app()->getRequest()->getRequestUri();
        if (strpos($url, '/returns/') === false && strpos($url, '/rma/') !== false) {
            $url = preg_replace('/rma/', 'returns', $url, 1);
            Mage::app()->getResponse()->setRedirect($url)->sendResponse();
            die;
        }
    }

    /**
     * Add comment to rma from helpdesk email.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onHelpdeskProcessEmail($observer)
    {
        $event = $observer->getEvent();
        $ticket = $event->getTicket();
        $customer = $event->getCustomer();
        $user = $event->getUser();

        $text = $event->getBody();
        if (!$rmaId = $ticket->getRmaId()) {
            return;
        }
        $rma = Mage::getModel('rma/rma')->load($rmaId);
        if (!$rma->getId()) {
            return;
        }

        $rma->addComment($text, false, $customer, $user, true, true, true, true);
    }

    /**
     * Save rma id to session when create exchange order in the backend.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onCreateOrderSessionQuoteInitialized($observer)
    {
        $session = $observer->getSessionQuote();
        if ($rmaId = Mage::app()->getRequest()->getParam('rma_id')) {
            $session->setRmaId($rmaId);
        }
    }

    /**
     * Save exchange order id to rma.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onCheckoutSubmitAllAfter($observer)
    {
        $order = $observer->getOrder();
        $session = Mage::getSingleton('adminhtml/session_quote');
        if ($rmaId = $session->getRmaId()) {
            $rma = Mage::getModel('rma/rma')->load($rmaId);
            $ids = $rma->getExchangeOrderIds();
            $ids[] = $order->getId();
            $rma->setExchangeOrderIds($ids);
            $rma->save();
            $session->unsetRmaId();
        }
    }

    /**
     * Save rma id to session when create credit memo in the backend.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onSalesOrderCreditmemoRegisterBefore($observer)
    {
        if ($rmaId = Mage::app()->getRequest()->getParam('rma_id')) {
            Mage::getSingleton('adminhtml/session')->setRmaId($rmaId);
        }
    }

    /**
     * Save credit memo id to rma.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onSalesOrderCreditmemoSaveAfter($observer)
    {
        $creditmemo = $observer->getDataObject();
        $session = Mage::getSingleton('adminhtml/session');
        if ($rmaId = $session->getRmaId()) {
            $rma = Mage::getModel('rma/rma')->load($rmaId);
            $ids = $rma->getCreditMemoIds();
            $ids[] = $creditmemo->getId();
            $rma->setCreditMemoIds($ids);
            $rma->save();
            $session->unsetRmaId();
        }
    }

    /**
     * Handle customer save procedure, and properly bind all his Guest RMA
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onCustomerSaveCommitAfter($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $rmas = Mage::getModel('rma/rma')->getCollection()
            ->addFieldToFilter('email', $customer->getEmail())
            ->addFieldToFilter('customer_id', array('null' => true));
        foreach ($rmas as $rma) {
            $rma->setCustomerId($customer->getId());
            $rma->save();
        }
    }

    /**
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Save rma id to session when create credit memo in the backend.
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function onCustomerLoginAfter($observer)
    {
        $this->_getSession()->setRmaGuestEmail('');
    }
}
