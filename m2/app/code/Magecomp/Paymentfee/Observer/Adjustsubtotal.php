<?php
namespace Magecomp\Paymentfee\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magecomp\Paymentfee\Model\Quote\Address\Total\PaymentfeeFactory;
use Magecomp\Paymentfee\Helper\Data;
use Magento\Tax\Model\Config;

class Adjustsubtotal implements ObserverInterface
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
        $block = $observer->getBlock();
        if($block instanceof \Magento\Tax\Block\Checkout\Subtotal) 
		{
            $totals = $block->getTotals();
            if(isset($totals['paymentfee'])) 
			{       
                if ($totals['paymentfee']->getAddress()->getSubtotalInclTax() > 0) 
				{
                    return;
                }                 
                $store = $totals['paymentfee']->getAddress()->getQuote()->getStore();
                if ($this->_modelConfig->displayCartSubtotalInclTax($store)) 
				{
                    $block->getTotal()->setValue($block->getTotal()->getValue() -
                            $totals['paymentfee']->getAddress()->getMcPaymentfeeTaxAmount());
                }
                $block->getTotal()->setValueInclTax($block->getTotal()->getValueInclTax() -
                        $totals['paymentfee']->getAddress()->getMcPaymentfeeTaxAmount());
            }
        }
    }     
}
