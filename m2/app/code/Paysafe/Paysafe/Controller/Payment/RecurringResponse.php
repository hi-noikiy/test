<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Payment;

class RecurringResponse extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * get payment response in the my payment information page
     * @return void
     */
    public function execute()
    {   
        $checkoutId = $this->getRequest()->getParam('id');
        $informationId = $this->getRequest()->getParam('information_id');
        $paymentMethod = $this->getRequest()->getParam('payment_method');
        $action = $this->getRequest()->getParam('action');
        $paysafeTokenChange = $this->getRequest()->getParam('paysafe_token_change');
        $paysafeTokenRegister = $this->getRequest()->getParam('paysafe_token_register');
        $paysafeToken = $this->getRequest()->getParam('paysafe_token');

        $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);

        $credentials = $this->helperCore->getGeneralCredentials(); 
        $credentials['environment'] = $this->paymentMethod->getSpecificConfiguration('environment');
 

       $recurringParameters = array();
        if ($action != '') {
            if ($action == 'register' || $action == 'register_on_checkout') {
                $recurringParameters = $this->getRecurringParameters();
                if(!empty($paysafeTokenRegister)){
                    $recurringParameters['card']['singleUseToken'] = $paysafeTokenRegister;
                }else{
                    $recurringParameters['card']['singleUseToken'] = $paysafeToken;
                }

                $response = $this->helperCore->getRegisterPayment($credentials, $recurringParameters);
            } elseif ($action == 'change') {
                $customerId = $this->getCustomerId();
                $registration = $this->information->getRegistrationByInformationId($customerId, $informationId);
                $profileId = $registration[0]['profile_id'];
                $cardId = $registration[0]['card_id'];
       
                $recurringParameters = $this->getRecurringParameters();
                $recurringParameters['card']['singleUseToken'] = $paysafeTokenChange;

                $response = $this->helperCore->getRegisterPayment($credentials, $recurringParameters);
                $this->helperCore->deleteRegistration($profileId, $cardId, $credentials);
            }
            if(!isset($response['error']['code'])) {
                $success = $this->savePaymentAccount($informationId, $response);
                if ($success) {
                    if ($informationId) {
                        $this->redirectSuccessRecurring('Congratulations, your payment information were successfully updated.');
                    } elseif ($this->getRequest()->getParam('order')) {
                        $this->_redirect('checkout/onepage/success', ['_secure' => true]);
                    } else {
                        $this->redirectSuccessRecurring('Congratulations, your payment information were successfully saved.');
                    }
                }
            } else {
                if ($action == 'register_on_checkout') {
                    $this->redirectError('We are sorry. Your attempt to save your payment information was not successful.: ' . $response['error']['message']);
                } else {
                    $this->redirectErrorRecurring(null, $response['error']['message'], $informationId);
                }
            }
        } else {
            $this->redirectError('An error occurred while processing');
        }
    }

    /**
     * save a payment account
     * @param  string $informationId
     * @param  array $response
     * @return void
     */
    protected function savePaymentAccount($informationId, $response)
    {
        $response['environment'] = $this->paymentMethod->getSpecificConfiguration('environment');
        $customerId = $this->getCustomerId();
        $registrationParameters = array_merge(
            $customerId,
            $response
        );

        if ($informationId) {
            $registration = $this->information->getRegistrationByInformationId($customerId, $informationId);
            $registrationParameters['customerId'] = $registration[0]['customer_id'];
            $registrationParameters['merchantCustomerId'] = $registration[0]['merchant_customer_id'];
            $registrationParameters['environment'] = $registration[0]['environment'];
            $registrationParameters['profileId'] = $registration[0]['profile_id'];
            $registrationParameters['email'] = $registration[0]['email'];

            $this->information->updateRegistration($registrationParameters, $informationId);
        } else {
            $this->information->insertRegistration($registrationParameters);
        }
        return true;
    }

}
