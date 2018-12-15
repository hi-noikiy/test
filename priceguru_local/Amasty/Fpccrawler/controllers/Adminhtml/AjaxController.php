<?php

/**
 * @copyright   Copyright (c) 2009-2014 Amasty (http://www.amasty.com)
 */
class Amasty_Fpccrawler_Adminhtml_AjaxController extends Mage_Adminhtml_Controller_Action
{

    public function generateAction()
    {
        /** @var Amasty_Fpccrawler_Model_Observer $observer */
        $observer = Mage::getModel('amfpccrawler/observer');
        $observer->generateQueue();


        $msg = Mage::helper('amfpccrawler')->__('Queue was generated.');
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);

        $this->_redirect('adminhtml/system_config/edit/section/amfpccrawler');

        return true;
    }

    public function processAction()
    {
        /** @var Amasty_Fpccrawler_Model_Observer $observer */
        $observer = Mage::getModel('amfpccrawler/observer');
        $observer->processQueue();

        $msg = Mage::helper('amfpccrawler')->__('Queue was processed.');
        Mage::getSingleton('adminhtml/session')->addSuccess($msg);

        $this->_redirect('adminhtml/system_config/edit/section/amfpccrawler');

        return true;
    }

}
