<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Rest_Customer_V1 extends EM_Apiios_Model_Api2_Checkout_Onepage_Rest_Abstract
{
    protected $_customer = null;

    public function  _retrieve() {
        $step = $this->getRequest()->getParam('step','login');
        if($step == 'login'){
            Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
            //Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
            $this->getOnepage()->initCheckout();
            return array(
                'update_section'  =>  array(
                    'name'  =>  'checkout_method',
                    'json_form' =>  $this->getCheckoutMethodFields()
                )

            );
        } else {
            return parent::_retrieve();
        }
    }


    protected function initCustomerSession(){
        Mage::getSingleton('customer/session')->setCustomer($this->getCustomer());
        return $this;
    }
}
?>