<?php
class EM_Apiios_Model_Api2_Customer_Form_Rest_Customer_V1 extends EM_Apiios_Model_Api2_Customer_Form_Rest_Abstract
{
    protected $_customer = null;
    /**
     * Load fields edit account information
     * @return EM_Apiios_Model_Api2_Customer_Form_Rest_Customer_V1
     */
    protected function  _retrieve() {
        $typeAction = $this->getRequest()->getParam('type_action');
        if($typeAction != 'logout'){
            $result = parent::_retrieve();
            $result['list_field']['login']['title'] = Mage::helper('customer')->__('Change Password');
            //$customer = Mage::getModel('customer/customer')->setStoreId($this->_getStore()->getId())->load($this->getApiUser()->getUserId());
            $customer = $this->getCustomer();
            $customer->setChangePassword(0);
            $data = $customer->getData();
            if(isset($data['confirmation']))
                unset ($data['confirmation']);
            if(isset($data['password']))
                unset ($data['password']);
            if(isset($data['current_password']))
                unset ($data['current_password']);
            $result['list_field']['customer'] = $data;
        } 
        return $result;
    }

    protected function  _loadLoginFields() {
        /* Login Information */
        $login = array();
        $helper = Mage::helper('customer');

        /* Change Password Field */
        $field = array();
        $field['label'] = $helper->__('Change Password');
        $field['required'] = false;
        $field['name'] = 'change_password';
        $field['type'] = 'checkbox';
        $login[] = $field;

        /* Old Password Field */
        $field = array();
        $field['label'] = $helper->__('Current Password');
        $field['required'] = false;
        $field['name'] = 'current_password';
        $field['type'] = 'password';
        $login[] = $field;

        /* Current Password Field */
        $field = array();
        $field['label'] = $helper->__('New Password');
        $field['required'] = true;
        $field['name'] = 'password';
        $field['type'] = 'password';
        $login[] = $field;

        /* Confirmation password Field */
        $field = array();
        $field['label'] = $helper->__('Confirm New Password');
        $field['required'] = true;
        $field['name'] = 'confirmation';
        $field['type'] = 'password';
        $login[] = $field;

        return $login;
    }

    /**
     * Get Current Customer
     * @return Mage_Customer_Model_Customer
     */
    protected function getCustomer(){
        if(!$this->_customer)
            $this->_customer = Mage::getModel('customer/customer')->setStoreId($this->_getStore()->getId())->load($this->getApiUser()->getUserId());
        return $this->_customer;
    }

    /**
     * Update customer. Method : PUT.
     *
     * @param array $data
     * @return string
     */
    public function _update(array $customerData){
        /* Translate text */
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);

        $customer = $this->getCustomer();
        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form')->setStore($this->_getStore());
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        /**
         * Initialize customer group id
         */
        $customer->getGroupId();

        try {
            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);

                // If password change was requested then add it to common validation scheme
                if (isset($customerData['change_password']) && $customerData['change_password']) {
                    $currPass   = $customerData['current_password'];
                    $newPass    = $customerData['password'];
                    $confPass   = $customerData['confirmation'];

                    $oldPass = $this->getCustomer()->getPasswordHash();
                    if (Mage::helper('core/string')->strpos($oldPass, ':')) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }

                    if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                        if (strlen($newPass)) {
                            /**
                             * Set entered password and its confirmation - they
                             * will be validated later to match each other and be of right length
                             */
                            $customer->setPassword($newPass);
                            $customer->setConfirmation($confPass);
                        } else {
                            $errors[] = Mage::helper('customer')->__('New password field cannot be empty.');
                        }
                    } else {
                        $errors[] = Mage::helper('customer')->__('Invalid current password');
                    }
                }

                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($customerErrors, $errors);
                }
            }

            $validationResult = count($errors) == 0;

            if (true === $validationResult) {
                $customer->save();
                if ($customer->isConfirmationRequired()) {
                    $customer->sendNewAccountEmail(
                        'confirmation',
                        Mage::getUrl('customer/account/logout'),
                        $this->_getStore()->getId()
                    );
                }
                $this->_successMessage(
                   self::USER_UPDATE_SUCCESS,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('id'=>$customer->getId())
                );
            } else {
                if (is_array($errors)) {
                    foreach ($errors as $errorMessage) {
                        $this->_errorMessage($errorMessage, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    }
                } else {
                    $this->_errorMessage(Mage::helper('customer')->__('Invalid customer data'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }
            }
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $message = Mage::helper('customer')->__('There is already an account with this email address. If you are sure that it is your email address, click here to get your password and access your account.', $url);
                $this->_errorMessage($message, $e->getCode());
            } else {
                $message = $e->getMessage();
                $this->_errorMessage($message, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        } catch (Exception $e) {
            $this->_errorMessage(Mage::helper('customer')->__('Cannot save the customer.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $this->_render($this->getResponse()->getMessages());
        $this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
        //return $this->_getLocation($customer);
    }

    /**
     * Logout, reject token. Method : DELETE.
     */
    public function  _delete() {
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        Mage::getSingleton('customer/session')->setCustomer($this->getCustomer())->logout();
        $this->_successMessage(
                   Mage::helper('apiios')->__('Your are logout'),
                    Mage_Api2_Model_Server::HTTP_OK
                );
        $this->_render($this->getResponse()->getMessages());
    }
}
?>