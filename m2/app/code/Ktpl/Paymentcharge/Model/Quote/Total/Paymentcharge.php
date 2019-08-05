<?php

namespace Ktpl\Paymentcharge\Model\Quote\Total;

class Paymentcharge extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;
    protected $_helper;
    protected $logger;
    private $paymentMethodManagement;
    /**
     * Custom constructor.
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
            \Ktpl\Paymentcharge\Helper\Data $_helper, 
            \Psr\Log\LoggerInterface $logger,
            \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
            \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) { 
        $this->_priceCurrency = $priceCurrency;
        $this->_helper = $_helper;
        $this->logger = $logger;
        $this->paymentMethodManagement = $paymentMethodManagement;
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
        $baseDiscount = $this->_helper->calculatePaymentcharge($total->getSubtotal(), $customergroup);
                
        if ($this->_canApplyTotal($quote) && $baseDiscount) {
            //$baseDiscount = $total->getSubtotal() * $dis / 100;
            $discount = $this->_priceCurrency->convert($baseDiscount);
            $total->addTotalAmount('payment_charge', $discount);
            $total->addBaseTotalAmount('payment_charge', $baseDiscount);
            //$total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseDiscount);
            $total->setPaymentCharge($discount);
            $total->setBasePaymentCharge($discount);
            $quote->setPaymentCharge($discount);
            $quote->setBasePaymentCharge($discount);
            $address->setPaymentCharge($discount);
            $address->setBasePaymentCharge($discount);

        }
        return $this;
    }
    
    /**
     * Return true if can apply totals
     * @param Quote $quote
     * @return bool
     */
    protected function _canApplyTotal(\Magento\Quote\Model\Quote $quote)
    {
        if (!$quote->getId()) {
            return false;
        }
        $paymentMethodsList = $this->paymentMethodManagement->getList($quote->getId());
        if ((count($paymentMethodsList) == 1) && (current($paymentMethodsList)->getCode() == 'classyllama_llamacoin')) {
            return true;
        }

        return ($quote->getPayment()->getMethod() == 'classyllama_llamacoin');
    }

}
