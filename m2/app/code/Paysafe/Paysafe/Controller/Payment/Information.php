<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Controller\Payment;

class Information extends \Paysafe\Paysafe\Controller\Payment
{
    /**
     * display the my payment information page
     * @return object
     */
    public function execute()
    {
    	if (!$this->customer->isLoggedIn()) {
    		$this->_redirect('customer/account/login');
    	} else {
            $resultPageFactory = $this->resultPageFactory->create();
            $resultPageFactory->getConfig()->getTitle()->set(__('My Payment Information'));
    		$block = $resultPageFactory->getLayout()->getBlock('paysafe_payment_information');

            $isRecurringActive = $this->helperCore->getGeneralCredentials()['recurring'];
            $block->setIsRecurringActive($isRecurringActive);

            if ($isRecurringActive) {
                $this->method = $this->createPaymentMethodObjectByPaymentMethod('paysafe_creditcard');
                $isActive = $this->method->getSpecificConfiguration('active');
                $block->setIsCreditCardActive($isActive);
                if ($isActive) {
                    $block->setCustomerDataCreditCard(
                        $this->information->getPaymentInformation($this->getCustomerId())
                    );
                }

                $block->setRegisterPaymentUrl($this->_url->getUrl('paysafe/payment/registerrecurringaccount', ['_secure' => true]));
                $block->setChangePaymentUrl($this->_url->getUrl('paysafe/payment/changerecurringaccount', ['_secure' => true]));
                $block->setDeletePaymentUrl($this->_url->getUrl('paysafe/payment/deleterecurringaccount', ['_secure' => true]));
            }

            return $resultPageFactory;
        }
    }
}
