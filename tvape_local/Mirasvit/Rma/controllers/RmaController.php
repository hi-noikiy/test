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

class Mirasvit_Rma_RmaController extends Mirasvit_Rma_AbstractRmaController
{
    /**
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     */
    public function indexAction()
    {
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        if (!$customer->getId()) {
            $this->_redirect('rma/rma/new');

            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     */
    public function orderAction()
    {
        if ($orderId = $this->getRequest()->getParam('order_id')) {
            $order = Mage::getModel('sales/order')->load($orderId);
            $customer = $this->_getSession()->getCustomer();
            if ($order->getCustomerId() == $customer->getId()) {
                Mage::register('current_order', $order);
                $this->loadLayout();
                $this->_initLayoutMessages('customer/session');
                $this->renderLayout();

                return;
            }
        }
        $this->norouteAction();
    }

    /**
     * @param Mirasvit_Rma_Model_Rma $rma
     *
     * @return string
     */
    protected function getRmaViewUrl($rma)
    {
        return Mage::helper('rma/url')->getRmaViewUrl($rma->getId());
    }

    /**
     * @return string
     */
    protected function getRmaListUrl()
    {
        return Mage::helper('rma/url')->getRmaListUrl();
    }
}
