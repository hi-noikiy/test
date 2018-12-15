<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */
class Amasty_StoreCredit_Adminhtml_Amstcred_HistoryController extends Mage_Adminhtml_Controller_Action
{

    public function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('reports')->__('Reports'), Mage::helper('reports')->__('Reports'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('Credit Transactions History'));
        //$this->_setActiveMenu('report/amstcred_history');
        $this->_initAction()
            ->_setActiveMenu('report/amstcred')
            ->_addBreadcrumb(Mage::helper('amstcred')->__('Credit Transactions History'), Mage::helper('amstcred')->__('Credit Transactions History'));
        //$this->loadLayout();
        $this->renderLayout();
    }


    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('amstcred/adminhtml_history_grid')->toHtml()
        );
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('report/amstcred');
    }

}
