<?php
class Gearup_Sds_AutoresponderController extends Mage_Core_Controller_Front_Action
{
    public function autologinAction()
    {
        $params = $this->getRequest()->getParams();
        $url = base64_decode($params['url']);
        if ($params['cid']) {
            if (Mage::getStoreConfig('ebizmarts_autoresponder/review/autologin')) {
                $customer = Mage::getModel('customer/customer')->load(base64_decode($params['cid']));
                if ($customer->getId()) {
                    Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                }
                $this->_redirectUrl($url);
            } else {
                if (Mage::helper('customer')->isLoggedIn()) {
                    $this->_redirectUrl($url);
                } else {
                    $this->_redirectUrl($url);
                }
            }
        } else {
            $this->_redirectUrl($url);
        }
    }
}