<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ktpl\Guestabandoned\Plugin\Checkout\Model;

class ShippingInformationManagement
{
    protected $quoteRepository;
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }
    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
	    	$address = $addressInformation->getShippingAddress();
	        $firstName = $address->getFirstName();
	        $lastName = $address->getLastName();
            $status = 1;//$address->getstatus();
            $quote = $this->quoteRepository->get($cartId); 
			$quote->setData('customer_firstname', $firstName);
			$quote->setData('customer_lastname', $lastName);
            $quote->setData('status', $status);
			$this->quoteRepository->save($quote);
    }
}
