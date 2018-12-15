<?php
class EM_Apiios_Model_Api2_Customer_Form_Rest_Guest_V1 extends EM_Apiios_Model_Api2_Customer_Form_Rest_Abstract
{
    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

	protected function getInforSocialUser($data){
		require_once Mage::getBaseDir('lib') . DS . 'em' . DS . 'apiios' . DS . 'RNCryptor' . DS . 'autoload.php';
		$result = array();
		$consumerSecret = 'yn4eg3wpah88f89cjvpyjrhwaha3pqwf';
		$cryptor = new \RNCryptor\Decryptor();
		if(!isset($data['access_token'])){
			$result['error'] = 1;
			return $result;
		}
		$accessTokenEncrypted = $data['access_token'];
		if($data['os'] == 'ios')
			$accessToken = Zend_Json::decode($cryptor->decrypt($accessTokenEncrypted, $consumerSecret));
		else {
			$accessToken = $accessTokenEncrypted;
		}	
		$network = $accessToken['type'];
		if(isset($accessToken['token'])){
			$token = $accessToken['token'];
			/* Check valid access token before call facebook api */
			$prefix = 'CS_';
			$lengthPrefix = strlen($prefix);
			if(substr($token,0,$lengthPrefix) != $prefix){
				$result['error'] = 1;
				return $result;
			}
			$token = substr($token,3,strlen($token) - $lengthPrefix);
		}
		
		$parse = array('error' => 1);
		switch($network){
			case 'facebook' :
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?access_token=$token");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

				$response = curl_exec($ch);
				curl_close($ch);
				$parse = Zend_Json::decode($response);
				break;
			case 'google' :	
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=$token");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

				$response = curl_exec($ch);
				curl_close($ch);
				$parse = Zend_Json::decode($response);
				if(!isset($parse['error'])){
					$parse['first_name'] = $parse['given_name'];
					$parse['last_name'] = $parse['family_name'];
				}
				break;
			case 'linkedin' :	
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, "https://api.linkedin.com/v1/people/~:(first-name,last-name,email-address)?format=json&oauth2_access_token=$token");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

				$response = curl_exec($ch);
				curl_close($ch);
				$parse = Zend_Json::decode($response);
				if(!isset($parse['errorCode'])){
					$parse['first_name'] = $parse['firstName'];
					$parse['last_name'] = $parse['lastName'];
					$parse['email'] = $parse['emailAddress'];
				}
				break;	
			case 'twitter' :
				$parse = $accessToken;
				break;	
			case 'yahoo' :
				$parse = $accessToken;
				break;					
			default :
				break;
		}
		
		if(isset($parse['error'])){
			$result['error'] = 1;
		} else {
			$result = array(
				'first_name' => $parse['first_name'],
				'last_name' => $parse['last_name'],
				'email' => $parse['email']
			);
		}
		return $result;
	}
	
	/**
   * Get the header info to store.
   */
  public function getHeaderTwitter($ch, $header) {
    $i = strpos($header, ':');
    if (!empty($i)) {
      $key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
      $value = trim(substr($header, $i + 2));
      $this->http_header[$key] = $value;
    }
    return strlen($header);
  }
	
    /**
     * Login function (METHOD : PUT)
     */
    public function  _update(array $data) {
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $type = trim(str_replace('/ios/customer/account/','',$this->getRequest()->getPathInfo()),'/');
		//$type = $this->getRequest()->getParam('method');
        if($type == 'loginPut' || $type == 'loginSocialPut'){
            if(!isset($data['ignore_captcha'])){
                $formId = 'user_login';
                $captchaModel = Mage::helper('apiios/captcha')->setStore($this->_getStore())->getCaptcha($formId)->setStore($this->_getStore());
                if ($captchaModel->isRequired()) {
                    if (!isset($data['login_captcha']) || !$captchaModel->isCorrect($data['login_captcha'])) {
                        //$this->_error(Mage::helper('captcha')->__('Incorrect CAPTCHA.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                        throw new Exception(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                    }
                }
            }
            //Mage::app()->setCurrentStore($this->_getStore()->getId());
			$session = $this->_getSession();
            if ((!empty($data['login_username']) && !empty($data['login_password'])) || ($type == 'loginSocialPut')) {
                try {
                    Mage::getSingleton('core/cookie')->set('frontend',Mage::getSingleton('core/cookie')->get('PHPSESSID'));
                    if($type == 'loginPut'){
                        $session->login($data['login_username'], $data['login_password']);
					}	
                    elseif($type == 'loginSocialPut'){
						$socialInformation = $this->getInforSocialUser($data);
						if(isset($socialInformation['error'])){
							throw new Exception(Mage::helper('apiios')->__('Invalid username or password.'));
						} else {
							$customer = Mage::getModel("customer/customer");
							$customer->setWebsiteId($this->_getStore()->getWebsiteId());
							$customer->loadByEmail($socialInformation['email']);
							
							if(!$customer->getId()){
								$pass = $this->generatePassword(10);
								//if($send == 1) $this->sentMail($user,$pass);
								$customer->setFirstname($socialInformation['first_name'])
									->setLastname($socialInformation['last_name'])
									->setEmail($socialInformation['email'])
									->setPassword($pass)
									->setId(null)
									->save();
								$customer->setConfirmation('');
								$customer->getResource()->saveAttribute($customer,'confirmation');
								$customer->sendNewAccountEmail(
									'registered',
									'',
									$this->_getStore()->getId()
								);
								//$session->addSuccess($this->__('Thank you for registering with Main Store.'));
							}
							$tmp = $session->loginById($customer->getId());
							$session->renewSession();
						}
                    }
					/*if(!$session->getCustomer()->getId()){
						throw new Exception(Mage::helper('apiios')->__('login is unsuccessful.'));
					} else {*/
						if ($session->getCustomer()->getIsJustConfirmed()) {
							$this->_welcomeCustomer($session->getCustomer(), true);
						} else {
							$this->_successMessage(
							   Mage::helper('customer')->__('login success'),
								Mage_Api2_Model_Server::HTTP_OK,
								array('id'=>$session->getCustomer()->getId())
							);
							//$this->confirm($session->getCustomer());
						}
					//}
                } catch (Mage_Core_Exception $e) {
                    switch ($e->getCode()) {
                        case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                            $value = Mage::helper('customer')->getEmailConfirmationUrl($data['login_username']);
                            $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value);
                            $this->_errorMessage($message, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                            break;
                        case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                            $message = $e->getMessage();
                            $this->_errorMessage($message, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                            break;
                        default:
                            $message = $e->getMessage();
                            $this->_errorMessage($message, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    }
                } catch (Exception $e) {
                    // Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
                }
            } else {
                $this->_errorMessage(Mage::helper('customer')->__('Login and password are required.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        } else if($type == 'forgotpasswordPut'){
            $email = $data['email'];
            if ($email) {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    $this->_errorMessage(Mage::helper('customer')->__('Invalid email address.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                }

                $formId = 'user_forgotpassword';
                $captchaModel = Mage::helper('apiios/captcha')->setStore($this->_getStore())->getCaptcha($formId)->setStore($this->_getStore());
                if ($captchaModel->isRequired()) {
                    if (!$captchaModel->isCorrect($data['captcha_user_forgotpassword'])) {
                        //$this->_error(Mage::helper('captcha')->__('Incorrect CAPTCHA.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                        throw new Exception(Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
                    }
                }

                /** @var $customer Mage_Customer_Model_Customer */
                $customer = Mage::getModel('customer/customer')
                    ->setWebsiteId($this->_getStore()->getWebsiteId())
                    ->loadByEmail($email);

                if ($customer->getId()) {
                    try {
                        $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                        $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                        $customer->sendPasswordResetConfirmationEmail();
                    } catch (Exception $exception) {
                        throw new Mage_Api2_Exception($exception->getMessage(),300);
                        //$this->_errorMessage($exception->getMessage(), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
                    }
                }
                $this->_successMessage(
                   Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email)),
                   Mage_Api2_Model_Server::HTTP_OK
                );
            } else {
                throw new Mage_Api2_Exception(Mage::helper('customer')->__('Please enter your email.'),300);
                //$this->_errorMessage(Mage::helper('customer')->__('Please enter your email.'), Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
        }
        
        $this->_render($this->getResponse()->getMessages());
        //$this->getResponse()->setHttpResponseCode(Mage_Api2_Model_Server::HTTP_MULTI_STATUS);
    }

    /**
     * Generate password
     *
     * @param int $length
     * @return string
     */
    protected function generatePassword($length = 8) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result	=	substr(str_shuffle($chars),0,$length);
        return $result;
    }

    /**
     * Add welcome message and send new account email.
     * Returns success URL
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param bool $isJustConfirmed
     * @return string
     */
    protected function _welcomeCustomer(Mage_Customer_Model_Customer $customer, $isJustConfirmed = false)
    {
        $this->_successMessage(
           Mage::helper('customer')->__('Thank you for registering with %s.', $this->_getStore()->getFrontendName()),
            Mage_Api2_Model_Server::HTTP_OK,
            array('id'=>$customer->getId())
        );
        $customer->sendNewAccountEmail(
            $isJustConfirmed ? 'confirmed' : 'registered',
            '',
            $this->_getStore()->getId()
        );
    }

    /**
     * Create customer (METHOD : POST)
     *
     * @param array $customerData
     * @return string
     */
    public function _createCustomer(array $customerData){
        Mage::app()->setCurrentStore($this->_getStore()->getId());
        Mage::app()->loadAreaPart('frontend',  Mage_Core_Model_App_Area::PART_TRANSLATE);
        $this->checkUserCreate($customerData);
        $customer = Mage::getModel('customer/customer')->setStore($this->_getStore())->setId(null);
        /* @var $customerForm Mage_Customer_Model_Form */
        $customerForm = Mage::getModel('customer/form')->setStore($this->_getStore());
        $customerForm->setFormCode('customer_account_create')
            ->setEntity($customer);

        if (isset($customerData['is_subscribed'])) {
            $customer->setIsSubscribed(1);
        }

        /**
         * Initialize customer group id
         */
        $customer->getGroupId();

        try {
            $errors = array();
            /* @var $customerErrors array */
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                $customer->setPassword($customerData['password']);
                $customer->setConfirmation($customerData['confirmation']);
                
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
                   self::USER_CREATE_SUCCESS,
                    Mage_Api2_Model_Server::HTTP_OK,
                    array('id'=>$customer->getId())
                );
            } else {
                if (is_array($errors)) {
                    throw new Mage_Api2_Exception(implode('.',$errors),300);
                } else {
                    throw new Mage_Api2_Exception(Mage::helper('customer')->__('Invalid customer data'),300);
                }

            }
        } catch (Mage_Core_Exception $e) {
            if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                $message = Mage::helper('customer')->__('There is already an account with this email address. If you are sure that it is your email address, click here to get your password and access your account.');
                throw new Mage_Api2_Exception($message,300);
            } else {
                throw new Mage_Api2_Exception($e->getMessage(),300);
            }
        } catch (Exception $e) {
            throw new Mage_Api2_Exception(Mage::helper('customer')->__('Cannot save the customer.'),300);
        }
    }

    /**
     * Create new customer (for ios)
     *
     * @param array $data
     * @return string|void
     */
    protected function _create(array $data){
        $this->_createCustomer($data);
        $this->_render($this->getResponse()->getMessages());
    }

    /**
     * Create new customer (for android)
     *
     * @param array $data
     */
    protected function _multiCreate($data){
        $this->_createCustomer($data[0]);
    }

    /**
     * Check Captcha On Register User Page
     *
     * @param array $data
     * @return EM_Apiios_Model_Api2_Customer_Form_Rest_Guest_V1
     */
    public function checkUserCreate($data)
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