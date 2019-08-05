<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Payment;

class ChangeRecurringAccount extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * change a payment account
     * @return void
     */
    public function execute()
    {
    	if (!$this->customer->isLoggedIn()) {
    		$this->_redirect('customer/account/login');
    	} else {
            $informationId = $this->getRequest()->getParam('information_id');
    		$paymentMethod = $this->getRequest()->getParam('payment_method');

            if (!isset($informationId) && !isset($paymentMethod)) {
                $this->redirectErrorRecurring('Error Before Redirect');
            }

            $this->paymentMethod = $this->createPaymentMethodObjectByPaymentMethod($paymentMethod);

            return $this->getResultPageFactory($informationId);
        }
    }

    /**
     * display a payment widget form to change a payment account
     * @param string $informationId
     * @return object
     */
    protected function getResultPageFactory($informationId)
    {
        $resultPageFactory = $this->resultPageFactory->create();
        $resultPageFactory->getConfig()->getTitle()->set(__('Change Payment Information'));

        $this->setPageAsset($resultPageFactory);

        $block = $resultPageFactory->getLayout()->getBlock('paysafe_payment_change');

        $cancelUrl = $this->_url->getUrl($this->myPaymentInformationUrl, ['_secure' => true]);
        $responseUrl = $this->_url->getUrl($this->recurringResponseUrl,
            [
                'payment_method' => $this->paymentMethod->getCode(),
                'action' => 'change',
                'information_id' => $informationId,
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
