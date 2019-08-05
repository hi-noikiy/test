<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Payment;

class RegisterRecurringAccount extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * register a payment account in my payment information page
     * @return void
     */
    public function execute()
    {
    	if (!$this->customer->isLoggedIn()) {
    		$this->_redirect('customer/account/login');
    	} else {
    		$paymentMethod = $this->getRequest()->getParam('payment_method');

            if (!isset($paymentMethod)) {
                $this->redirectErrorRecurring('Error Before Redirect');
            }

            $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);

            return $this->getResultPageFactory();
        }
    }

    /**
     * display a payment widget form to register a payment account
     * @return object
     */
    protected function getResultPageFactory()
    {
        $resultPageFactory = $this->resultPageFactory->create();
        $resultPageFactory->getConfig()->getTitle()->set(__('Save Payment Information'));

        $this->setPageAsset($resultPageFactory);

        $block = $resultPageFactory->getLayout()->getBlock('paysafe_payment_register');

        $cancelUrl = $this->_url->getUrl($this->myPaymentInformationUrl, ['_secure' => true]);
        $responseUrl = $this->_url->getUrl($this->recurringResponseUrl,
            [
                'payment_method' => $this->paymentMethod->getCode(),
                'action' => 'register',
                '_secure' => true
            ]
        );

        $generalCredentials = $this->helperCore->getGeneralCredentials();
        $block->setBrand($this->paymentMethod->getBrand());
        $block->setApiKey($this->helperCore->getApiKey());
        $block->setEnvironment($this->paymentMethod->getSpecificConfiguration('environment'));
        $block->setPaymentResponseUrl($responseUrl);
        $block->setCancelUrl($cancelUrl);

        return $resultPageFactory;
    }
}
