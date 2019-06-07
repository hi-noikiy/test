<?php

/**
 * AttributeBrowser Controller Router
 */
class Hatimeria_AttributeBrowser_Controller_Router extends Mage_Core_Controller_Varien_Router_Abstract
{
    /**
     * Initialize Controller Router
     *
     * @param Varien_Event_Observer $observer
     */
    public function initControllerRouters($observer)
    {
        /* @var $front Mage_Core_Controller_Varien_Front */
        $front = $observer->getEvent()->getFront();

        $front->addRouter('attributebrowser', $this);
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param Zend_Controller_Request_Http $request
     * @return bool
     */
    public function match(Zend_Controller_Request_Http $request)
    {
        if (!Mage::isInstalled()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect(Mage::getUrl('install'))
                ->sendResponse();
            exit;
        }
        
        $browserConfig = Mage::getSingleton('attributebrowser/config');
        /* @var $browserConfig Hatimeria_AttributeBrowser_Model_Config */
        
        $identifier = trim($request->getPathInfo(), '/');
        
        if (strpos($identifier, '/') === false)
        {
            $prefix = $identifier;
            $key = false;
        }
        else
        {
            list ($prefix, $key) = explode('/', $identifier);
        }
        
        $attributeCode = $browserConfig->getAttributeCodeByRoute($prefix);
        if (!$attributeCode)
        {
            return false;
        }

        $condition = new Varien_Object(array(
            'identifier' => $identifier,
            'continue'   => true
        ));
        Mage::dispatchEvent('cms_controller_router_match_before', array(
            'router'    => $this,
            'condition' => $condition
        ));
        $identifier = $condition->getIdentifier();

        if ($condition->getRedirectUrl()) {
            Mage::app()->getFrontController()->getResponse()
                ->setRedirect($condition->getRedirectUrl())
                ->sendResponse();
            $request->setDispatched(true);
            
            return true;
        }

        if (!$condition->getContinue()) {
            return false;
        }
        
        if ($key)
        {
            if (!Mage::getSingleton('attributebrowser/list')->getAttributeItem($attributeCode, $key))
            {
                return false;
            }

            $request->setModuleName('attributebrowser')
                ->setControllerName('index')
                ->setActionName('browse')
                ->setParam('code', $attributeCode)
                ->setParam('key', $key);
        }
        else
        {
            $request->setModuleName('attributebrowser')
                ->setControllerName('index')
                ->setActionName('index')
                ->setParam('code', $attributeCode);
        }
        
        $request->setAlias(
            Mage_Core_Model_Url_Rewrite::REWRITE_REQUEST_PATH_ALIAS,
            $identifier
        );

        return true;
    }
}
