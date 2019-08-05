<?php

namespace Ktpl\Wholesaler\Model\Quote\Total;

class Tierdiscount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    protected $_helper;
    protected $logger;

    /**
     * Custom constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
            \Ktpl\Wholesaler\Helper\Data $_helper, 
            \Psr\Log\LoggerInterface $logger,
            \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) { 
        $this->_priceCurrency = $priceCurrency;
        $this->_helper = $_helper;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this|bool
     */
    public function collect(
            \Magento\Quote\Model\Quote $quote, 
            \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment, 
            \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        $address = $shippingAssignment->getShipping()->getAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->get('Magento\Customer\Model\Session');
        $customergroup = 0;
        if($customerSession->isLoggedIn()) {
            $customergroup = $customerSession->getCustomerGroupId();
        }
        $dis = $this->_helper->calculateCustomDiscount($total->getSubtotal(), $customergroup);
        
        if ($dis) {
            $baseDiscount = $total->getSubtotal() * $dis / 100;
            $discount = $this->_priceCurrency->convert($baseDiscount);
            $total->addTotalAmount('tier_discount', -$discount);
            $total->addBaseTotalAmount('tier_discount', -$baseDiscount);
            //$total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
            $total->setTierDiscount(-$discount);
            $total->setBaseTierDiscount(-$discount);
            $quote->setTierDiscount(-$discount);
            $quote->setBaseTierDiscount(-$discount);
            $address->setTierDiscount(-$discount);
            $address->setBaseTierDiscount(-$discount);
            
        }
        return $this;
    }

}
