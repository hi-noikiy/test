<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Payment;

class DeleteRecurringAccount extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * delete a payment account
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

            $customerId = $this->getCustomerId();
            $deleteInformation =
                $this->information->getRegistrationByInformationId($customerId, $informationId);

            if (!$deleteInformation) {
                $this->redirectErrorRecurring('Error Before Redirect');
            }
            return $this->getResultPageFactory($informationId, $deleteInformation);
        }
    }

    /**
     * display the page to delete a payment account
     * @param  string $informationId
     * @param  string $deleteInformation
     * @return object
     */
    protected function getResultPageFactory($informationId, $deleteInformation)
    {
        $resultPageFactory = $this->resultPageFactory->create();
        $resultPageFactory->getConfig()->getTitle()->set(__('Delete Payment Information'));

        $this->setPageAsset($resultPageFactory);

        $block = $resultPageFactory->getLayout()->getBlock('paysafe_payment_delete');

        $cancelUrl = $this->_url->getUrl($this->myPaymentInformationUrl, ['_secure' => true]);
        $responseUrl = $this->_url->getUrl('paysafe/payment/deleteresponse', ['_secure' => true]);

        $block->setCancelUrl($cancelUrl);
        $block->setResponseUrl($responseUrl);
        $block->setInformationId($informationId);
        $block->setPaymentMethod($this->paymentMethod->getCode());
        $block->setDeleteInformation($deleteInformation);

        return $resultPageFactory;
    }
}
