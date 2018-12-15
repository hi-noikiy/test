<?php

class Ktpl_Wholesaler_Model_Observer {

    public function checkstore($observer) {
        $action = $observer->getEvent()->getControllerAction()->getFullActionName();
        $act = explode('_', $action);
        if (Mage::app()->getStore()->getCode() == 'en_wholesaler') {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer->getGroupid() != 2) {
                    $url = Mage::getBaseUrl() . '?___store=default';
                    Mage::app()->getFrontController()->getResponse()->setRedirect($url);
                    Mage::app()->getResponse()->sendResponse();
                    exit;
                }
                if($action == 'catalog_product_view' || $action == 'checkout_cart_configure' || $action == 'cms_index_index'){
                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::helper('wholesaler')->Wholesalercategoryurl());
                    Mage::app()->getResponse()->sendResponse();
                    exit;
                }
            } else {
                if ($act[0] != 'customer' && $action != 'wholesaler_index_inquery' && $action !='wholesaler_index_inqueryPost') {
                    Mage::app()->getFrontController()->getResponse()->setRedirect(Mage::getUrl('customer/account'));
                    Mage::app()->getResponse()->sendResponse();
                    exit;
                } 
            }
        }
    }

    public function customerLogin($observer){

        $session = Mage::getSingleton('customer/session');
        $categoryId=$configValue = Mage::getStoreConfig('wholesaler/general/categoryid');
        if($categoryId==''){
            $categoryId=71;
        }
        $categoryurl=Mage::helper('wholesaler')->Wholesalercategoryurl();
        $customer = $observer->getCustomer();
        if ($customer->getGroupid() == 2) {
            if (Mage::app()->getStore()->getCode() != 'en_wholesaler') {
                $baseurl = Mage::getBaseUrl();
                $storeid = Mage::getModel('core/store')->loadConfig('en_wholesaler')->getId();
                $url = Mage::app()->getStore($storeid)->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK);
                $categoryurl = $url.str_replace($baseurl,"",$categoryurl);

                $cus_url = Mage::getUrl('wholesaler').'customer/account/login/?auth=true';
                Mage::app()->getFrontController()->getResponse()->setRedirect($cus_url);
                Mage::app()->getResponse()->sendResponse();
                exit;
            }
            $session->setAfterAuthUrl($categoryurl);
            $session->setBeforeAuthUrl('');
        }    
            
    }
    
    public function customerLogins($observer){
        $auth =  Mage::app()->getRequest()->getParam('auth');
        if(isset($auth))
            Mage::getSingleton('core/session')->addError("Please, Login in wholesaler store.");
    }

    public function assigngroup($observer){
        if (Mage::app()->getStore()->getCode() == 'en_wholesaler') {
            try {
                $customer = $observer->getCustomer();
                $customer->setData( 'group_id', 2 ); // Set the new customer group
            } catch ( Exception $e ) {
                Mage::log( "customer assign group failed: " . $e->getMessage() );
            }
        }    
    }

}
