<?php
class MindMagnet_AddonPopup_Model_Observer
{
    public function onCheckoutCartProductAddAfterRedirect(Varien_Event_Observer $observer)
    {
        // $addonAdded = Mage::app()->getRequest()->getParam('addon_added');
// 
        // $isPrimeCheckoutEnabled = Mage::helper('primecheckout')->isEnabled();
// 
        // if (isset($addonAdded) && $addonAdded = 'addon_added') {
            // $response = $observer->getResponse();
            // $response->setRedirect(($isPrimeCheckoutEnabled) ? Mage::getUrl('checkout/prime') : Mage::getUrl('checkout/onepage'));
            // Mage::getSingleton('checkout/session')->setNoCartRedirect(true);
        // }
    }
}