<?php

class Ktpl_Guestabandoned_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() 
    {
        $name = $this->getRequest()->getParam('name');
        $val = $this->getRequest()->getParam('valu');
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if (strpos($name, 'lastname') !== false) {
            $quote->setCustomerLastname(trim($val));
        } else if (strpos($name, 'firstname') !== false) {
            $quote->setCustomerFirstname(trim($val));
        } else if (strpos($name, 'username') !== false) {
            $quote->setCustomerEmail(trim($val));
        }
        try {
            $quote->save();
            echo 'success';
        } catch (Exception $ex) {
            Mage::log($ex->getMessage(), null, 'guestabandoned.log', true);
        }
    }

    
}
