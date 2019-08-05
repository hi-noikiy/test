<?php
namespace Magecomp\Paymentfee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magecomp\Paymentfee\Model\Quote\Address\Total\PaymentfeeFactory;
use Magecomp\Paymentfee\Helper\Data;
use Magento\Tax\Model\Config;

class Addpaymentfeefee implements ObserverInterface
{
    /**
     * @var \Magecomp\Paymentfee\Model\Quote\Address\Total\PaymentfeeFactory
     */
    protected $_totalPaymentfeeFactory;

    /**
     * @var \Magecomp\Paymentfee\Helper\Data
     */
    protected $_helperData;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_modelConfig;

    public function __construct(PaymentfeeFactory $totalPaymentfeeFactory, 
       Data $helperData, 
       Config $modelConfig)
    {
        $this->_totalPaymentfeeFactory = $totalPaymentfeeFactory;
        $this->_helperData = $helperData;
        $this->_modelConfig = $modelConfig;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        foreach ($quote->getAllAddresses() as $address) 
		{
            $this->_totalPaymentfeeFactory->create()->calculate($address);
        }
    }
}