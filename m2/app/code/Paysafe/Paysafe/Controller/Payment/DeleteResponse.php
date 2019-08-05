<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Payment;

class DeleteResponse extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * get the responses of payment account deleted
     * @return [type]
     */
    public function execute()
    {
        $informationId = $this->getRequest()->getParam('information_id');
        $paymentMethod = $this->getRequest()->getParam('payment_method');

        if (!isset($informationId) && !isset($paymentMethod)) {
            $this->redirectErrorRecurring('We are sorry. Your attempt to delete your payment information was not successful.', 'Error Before Redirect');
        }

        $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);
        $this->deleteRegistrationByInformationId($informationId);
    }

    /**
     * delete a payment registered by information id
     * @param  string $informationId
     * @return void
     */
    protected function deleteRegistrationByInformationId($informationId)
    {
        $customerId = $this->getCustomerId();
        $registration = $this->information->getRegistrationByInformationId($customerId, $informationId);
        $profileId = $registration[0]['profile_id'];
        $cardId = $registration[0]['card_id'];

        $credentials = $this->helperCore->getGeneralCredentials(); 
        $credentials['environment'] = $this->paymentMethod->getSpecificConfiguration('environment');

        $response = $this->helperCore->deleteRegistration($profileId, $cardId, $credentials);

        if (!isset($response['error']['code'])) {
            $this->information->deletePaymentInformationById($informationId);
            $this->redirectSuccessRecurring('Congratulations, your payment information were successfully deleted.');
        } else {
            $this->redirectErrorRecurring('We are sorry. Your attempt to delete your payment information was not successful.', $response['error']['message']);
        }
    }

}
