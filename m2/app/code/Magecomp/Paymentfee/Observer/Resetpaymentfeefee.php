<?php
namespace Magecomp\Paymentfee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magecomp\Paymentfee\Model\Quote\Address\Total\PaymentfeeFactory;
use Magecomp\Paymentfee\Helper\Data;
use Magento\Tax\Model\Config;

class Resetpaymentfeefee implements ObserverInterface
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
        if ($quote) 
		{
            $quote->setMcPaymentfeeAmount(0.0000);
            $quote->setBaseMcPaymentfeeAmount(0.0000);
            $quote->setMcPaymentfeeDescription();
            $addresses = $quote->getAllAddresses();
            if ($addresses) 
			{
                foreach ($addresses as $address) 
				{
                    $address->setMcPaymentfeeAmount(0.0000);
                    $address->setBaseMcPaymentfeeAmount(0.0000);
                }
            }
        }
    } 
}