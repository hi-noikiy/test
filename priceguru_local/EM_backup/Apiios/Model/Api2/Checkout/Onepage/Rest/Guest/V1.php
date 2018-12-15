<?php
class EM_Apiios_Model_Api2_Checkout_Onepage_Rest_Guest_V1 extends EM_Apiios_Model_Api2_Checkout_Onepage_Rest_Abstract
{
    protected $_customer = null;

    /*public function  __construct() {
        if(!Mage::registry('user_type'))
            Mage::register('user_type', 'guest');
        parent::__construct();
    }*/
    /**
     * Load fields edit account information and logout
     * @return EM_Apiios_Model_Api2_Checkout_Onpage_Rest_Guest_V1
     */
    
    protected function getCustomer(){
        return null;
    }

    /**
     * Method save (step 1)
     *
     * @param array $data
     * @return array
     */
    public function saveLogin($data){
        $method = $data['method'];
        $this->getOnepage()->saveCheckoutMethod($method);
        return array(
            'update_section'  =>  array(
                'name'  =>  'checkout_method',
                'json_form' =>  $this->getCheckoutMethodFields()
            )

        );
    }

    /**
     * Save billing action. Method : POST
     *
     * @param array $dataSubmit
     * @return array
     */
    /*public function saveBilling($dataSubmit){
        if(isset($dataSubmit['billing']['captcha_register_during_checkout'])){
            if(Mage::helper('apiios/captcha')->setStore($this->_getStore())->validate($dataSubmit['register_during_checkout'],'register_during_checkout'))
                return parent::saveBilling($dataSubmit);
        }

        if(isset($dataSubmit['billing']['captcha_guest_checkout'])){
            if(Mage::helper('apiios/captcha')->setStore($this->_getStore())->validate($dataSubmit['captcha_guest_checkout'],'captcha_guest_checkout'))
                return parent::saveBilling($dataSubmit);
        }
        return array();
    }*/

    /**
     * Check Captcha On Register User Page
     *
     * @param array $data
     * @return EM_Apiios_Model_Api2_Customer_Form_Rest_Guest_V1
     */
    public function checkCaptcha($data)
    {
        $formId = 'user_create';
        $captchaModel = Mage::helper('apiios/captcha')->setStore($this->_getStore())->getCaptcha($formId)->setStore($this->_getStore());
        if ($captchaModel->isRequired()) {
            if (!$captchaModel->isCorrect($data['captcha_user_create'])) {
                //$this->_error(Mage::helper('captcha')->__('Incorrect CAPTCHA.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                throw new Exception(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
            }
        }
        return $this;
    }
}
?>