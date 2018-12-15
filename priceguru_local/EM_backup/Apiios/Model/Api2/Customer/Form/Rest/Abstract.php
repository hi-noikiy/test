<?php
class EM_Apiios_Model_Api2_Customer_Form_Rest_Abstract extends Mage_Customer_Model_Api2_Customer_Rest
{
    const CHECK_USER_CREATE = 'This function is only for guest';
    const USER_UPDATE_SUCCESS = 'Your account is updated successful';
    const USER_CREATE_SUCCESS = 'Your account is created successful';

    /**
     * Retrieve field list of customer form
     *
     * @throws Mage_Api2_Exception
     * @return array
     */
    protected function _retrieve()
    {
        /* Translate text */
        //Mage::app()->setCurrentStore($this->_getStore()->getId());
        //Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $helper = Mage::helper('customer');
        $type = trim(str_replace('/ios/customer/account/','',$this->getRequest()->getPathInfo()),'/');
        if($type == 'login'){ /* Load login form */
            if($this->getUserType() != 'guest')
                return array(
                    'message_account' => $helper->__('You are logged in')
                );
            $fields = array();
            /* Email Address Field */
            $field = array();
            $field['label'] = $helper->__('Email Address');
            $field['required'] = true;
            $field['name'] = 'login_username';
            $field['type'] = 'text';
            $fields[] = $field;

            /* Password Field */
            $field = array();
            $field['label'] = $helper->__('Password');
            $field['required'] = true;
            $field['name'] = 'login_password';
            $field['type'] = 'password';
            $fields[] = $field;
            
            /* Captcha Field */
            $captcha = Mage::getModel('apiios/captcha_zend',array('formId' => 'user_login'))->setStore($this->_getStore());
            if($captcha->isRequired()){
                $captcha->generate();
                $field = array();
                $field['label'] = Mage::helper('captcha')->__('Please type the letters below');
                $field['required'] = true;
                $field['name'] = 'login_captcha';
                $field['type'] = 'captcha';
                $field['img_src'] = $captcha->getImgSrc();
                $fields[] = $field;
            }

            return array(
                'list_field' => array(
                    'title'     =>  $helper->__('Registered Customers'),
                    'tooltip'   =>  $helper->__('If you have an account with us, please log in.'),
                    'fields'    =>  $fields
                )
            );

        } else if($type == 'create'){ /* Load create form */
            list($persional,$login) = $this->_loadFields();
            return array('list_field' => array(
                'personal'  =>  array(
                        'title' =>  $helper->__('Personal Information'),
                        'fields'=>  $persional
                ),
                'login'     =>  array(
                        'title' =>  $helper->__('Login Information'),
                        'fields'=>  $login
                )
            ));
        } else if($type == 'forgotpassword'){ /* Load forgotpassword form */
            if($this->getUserType() != 'guest')
                return array(
                    'message_account' => $helper->__('You are logged in')
                );
            
            $fields = array();
            /* Email Address Field */
            $field = array();
            $field['label'] = $helper->__('Email Address');
            $field['required'] = true;
            $field['name'] = 'email';
            $field['type'] = 'text';
            $fields[] = $field;

            /* Captcha Field */
            $captcha = Mage::getModel('apiios/captcha_zend',array('formId' => 'user_forgotpassword'))->setStore($this->_getStore());
            if($captcha->isRequired()){
                $captcha->generate();
                $field = array();
                $field['label'] = Mage::helper('captcha')->__('Please type the letters below');
                $field['required'] = true;
                $field['name'] = 'captcha_user_forgotpassword';
                $field['type'] = 'captcha';
                $field['img_src'] = $captcha->getImgSrc();
                $fields[] = $field;
            }

            return array('list_field' => array(
                'title'  => $helper->__('Retrieve your password here'),
                'tooltip'=> $helper->__('Please enter your email address below. You will receive a link to reset your password.'),
                'fields' => $fields
            ));
        } else {
            if($this->getUserType() != 'guest')
                return array(
                    'message_account' => $helper->__('You are logged in')
                );
            /* Refresh Captcha */
            $formId = $this->getRequest()->getParam('formId');
            $captcha = Mage::getModel('apiios/captcha_zend',array('formId' => $formId))->setStore($this->_getStore());
            if($captcha->isRequired()){
                $captcha->generate();
                $imgSrc = $captcha->getImgSrc();
                return array('imgSrc'=>$imgSrc);
            } else {
                return array(
                    'message_captcha_refresh' => $helper->__('Captcha is not required')
                );
            }
        }
    }

    /**
     * Get field list in create form via admin configuration
     * @return array
     */
    protected function _loadFields(){
        /* Personal Information */
        /* Get field from customer/widget_name block */
        $widgetName = Mage::getModel('apiios/api2_customer_widget_name')->setStore($this->_getStore());
        $persional = $widgetName->buildFieldList();
        $helper = Mage::helper('customer');

        /* Email Address Field */
        $field = array();
        $field['label'] = $helper->__('Email Address');
        $field['required'] = true;
        $field['name'] = 'email';
        $field['type'] = 'text';
        $persional[] = $field;

        /* is_subscribed field */
        if(Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter') && $this->getUserType() == 'guest'){
            $field = array();
            $field['label'] = $helper->__('Sign Up for Newsletter');
            $field['required'] = false;
            $field['name'] = 'is_subscribed';
            $field['type'] = 'checkbox';
            $persional[] = $field;
        }

        $dateBirth = Mage::getModel('apiios/api2_customer_widget_dob')->buildFieldList();
        if(!empty($dateBirth))
            $persional[] = $dateBirth;

        $persional = array_merge($persional,Mage::getModel('apiios/api2_customer_widget_taxvat')->buildFieldList());
        $persional = array_merge($persional,Mage::getModel('apiios/api2_customer_widget_gender')->buildFieldList());
        
        $login = $this->_loadLoginFields();
        return array($persional,$login);
    }

    /**
     * Load login field group in create account form
     * @return array
     */
    protected function _loadLoginFields(){
        /* Login Information */
        $login = array();
        $helper = Mage::helper('customer');
        /* Password Field */
        $field = array();
        $field['label'] = $helper->__('Password');
        $field['required'] = true;
        $field['name'] = 'password';
        $field['type'] = 'password';
        $login[] = $field;

        /* Confirmation password Field */
        $field = array();
        $field['label'] = $helper->__('Confirm Password');
        $field['required'] = true;
        $field['name'] = 'confirmation';
        $field['type'] = 'password';
        $login[] = $field;
       
        /* Captcha Field */
        $captcha = Mage::getModel('apiios/captcha_zend',array('formId' => 'user_create'))->setStore($this->_getStore());
        if($captcha->isRequired()){
            $captcha->generate();
            $field = array();
            $field['label'] = Mage::helper('captcha')->__('Please type the letters below');
            $field['required'] = true;
            $field['name'] = 'captcha_user_create';
            $field['type'] = 'captcha';
            $field['img_src'] = $captcha->getImgSrc();
            $login[] = $field;
        }
       
        return $login;
    }

}
?>