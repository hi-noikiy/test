<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Output_Login extends EM_Apiios_Model_Api2_Checkout_Onepage_Output_Abstract
{
    public function  __construct() {
        if (!$this->isCustomerLoggedIn()) {
            $this->getCheckout()->setStepData('login', array('label'=>Mage::helper('checkout')->__('Checkout Method'), 'allow'=>true));
        }
        parent::__construct();
    }

    public function toArrayFields(){
        /* Login Information */
        $checkoutType = array();
        $helper = Mage::helper('checkout');
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        //Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getOnepage()->initCheckout();
        if($this->getQuote()->isAllowedGuestCheckout()){
            $checkoutType['guest'] = $helper->__('Checkout as Guest');
        }
        $checkoutType['login'] = $helper->__('Login');
        $checkoutType['register'] = $helper->__('Register to Create an Account');
		$checkoutType['paypal_express']    =  $helper->__('Paypal Express');
        return array(
            'update_section'  =>  array(
                'name'  =>  'login',
                'json_form' =>  $checkoutType
            )

        );
    }
}
?>
