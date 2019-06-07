<?php

/**
 * AttributeBrowser IndexController
 */
class Hatimeria_AttributeBrowser_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Browse items (attribute options) 
     */
    public function indexAction()
    {
        $request = $this->getRequest();
        $model = Mage::getSingleton('attributebrowser/list');
        $model->setCurrentCode($request->getParam('code', false));
        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
    
    /**
     * Browse products action 
     */
    public function browseAction()
    {
        $request = $this->getRequest();
        $model = Mage::getSingleton('attributebrowser/list');
        $model
            ->setCurrentCode($request->getParam('code', false))
            ->setCurrentKey($request->getParam('key', false));
        
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }
}
