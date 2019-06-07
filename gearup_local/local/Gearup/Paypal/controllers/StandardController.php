<?php
require_once(Mage::getModuleDir('controllers','Mage_Paypal').DS.'StandardController.php');

class Gearup_Paypal_StandardController extends Mage_Paypal_StandardController
{
    public function cancelAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        if ($session->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($session->getLastRealOrderId());
            if ($order->getId()) {
                $order->cancel()->save();
            }
            Mage::helper('paypal/checkout')->restoreQuote();
        }
        //$this->_redirect('checkout/cart');
        $this->_redirect('checkout/onepage/index/goto/review');
    }

}
