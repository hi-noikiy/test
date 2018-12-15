<?php
require_once 'app'.DS.'code'.DS.'core'.DS.'Mage'.DS.'Customer'.DS.'controllers'.DS.'AccountController.php';
class EM_Apiios_Customer_AccountController extends Mage_Customer_AccountController
{  
    /**
     * Login post action
     */
    public function loginPostAction()
    {
        if ($this->_getSession()->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $post = $this->getRequest()->getPost();
        $formId = 'user_login';
        $store = Mage::app()->getStore($this->getRequest()->getParam('store'));
        $captchaModel = Mage::helper('apiios/captcha')->setStore($store)->getCaptcha($formId)->setStore($store);
        if ($captchaModel->isRequired()) {
            if (!$captchaModel->isCorrect($post['login_captcha'])) {
                //$this->_error(Mage::helper('captcha')->__('Incorrect CAPTCHA.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                throw new Exception(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
            }
        }

        $session = $this->_getSession();

        if ($this->getRequest()->isPost()) {
            
            $login = array('username'=>$post['login_username'],'password'=>$post['login_password']);
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $session->login($login['username'], $login['password']);
                    if ($session->getCustomer()->getIsJustConfirmed()) {
                        $this->_welcomeCustomer($session->getCustomer(), true);
                    }
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($login['username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            break;
                        default:
                            $message = $e->getMessage();
                    }
                    $session->addError($message);
                    $session->setUsername($login['username']);
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $session->addError($this->__('Login and password are required.'));
            }
        }

        $this->_loginPostRedirect();
    }
}
