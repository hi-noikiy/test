<?php
namespace Magecomp\Paymentfee\Block\Sales\Order;

use Magecomp\Paymentfee\Helper\Data as HelperData;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Totals as OrderTotals;

class Totals extends OrderTotals {
    /**
     * @var HelperData
     */
    protected $_helperData;

    public function __construct(Context $context, 
        Registry $registry, 
        HelperData $helperData, 
        array $data = [])
    {
        $this->_helperData = $helperData;

        parent::__construct($context, $registry, $data);
    }


    public function initTotals() 
	{
        if ($this->getSource()->getMcPaymentfeeAmount() != 0)
		{
            $parent = $this->getParentBlock();
            if ($this->_helperData->displayBothSales()) 
			{
                $paymentfee = new DataObject([
                            'code'  => 'paymentfee',
                            'value' => $this->getSource()->getMcPaymentfeeAmount(),
                            'base_value'=> $this->getSource()->getBaseMcPaymentfeeAmount(),
                            'label' => __('%1 (Excl. Tax)', $this->getSource()->getMcPaymentfeeDescription())
                        ]);
                $paymentfeeIncl = new DataObject([
                            'code'  => 'paymentfee_incl',
                            'value' => $this->getSource()->getMcPaymentfeeAmount() + $this->getSource()->getMcPaymentfeeTaxAmount(),
                            'base_value'=> $this->getSource()->getBaseMcPaymentfeeAmount()+ $this->getSource()->getBaseMcPaymentfeeTaxAmount(),
                            'label' => __('%1 (Incl. Tax)', $this->getSource()->getMcPaymentfeeDescription())
                        ]);
                //Work out where to slot in paymentfee totals
                if ($parent->getTotal('shipping_incl')) {
                    $parent->addTotal($paymentfee,'shipping_incl');
                } elseif ($parent->getTotal('shipping')) {
                    $parent->addTotal($paymentfee,'shipping');
                } elseif ($parent->getTotal('subtotal_incl')) {
                    $parent->addTotal($paymentfee,'subtotal_incl');
                } else {
                    $parent->addTotal($paymentfee,'subtotal');
                }
                //add the inclusive paymentfee after the excl paymentfee
                $parent->addTotal($paymentfeeIncl,'paymentfee');
            }
			elseif ($this->_helperData->displayIncludeTaxSales()) 
			{
                $paymentfeeIncl = new DataObject([
                            'code'  => 'paymentfee_incl',
                            'value' => $this->getSource()->getMcPaymentfeeAmount() + $this->getSource()->getMcPaymentfeeTaxAmount(),
                            'base_value'=> $this->getSource()->getBaseMcPaymentfeeAmount()+ $this->getSource()->getBaseMcPaymentfeeTaxAmount(),
                            'label' => $this->getSource()->getMcPaymentfeeDescription()
                        ]);
                //Work out where to slot in paymentfee totals
                if ($parent->getTotal('shipping_incl')) {
                    $parent->addTotal($paymentfeeIncl,'shipping_incl');
                } elseif ($parent->getTotal('shipping')) {
                    $parent->addTotal($paymentfeeIncl,'shipping');
                } elseif ($parent->getTotal('subtotal_incl')) {
                    $parent->addTotal($paymentfeeIncl,'subtotal_incl');
                } else {
                    $parent->addTotal($paymentfeeIncl,'subtotal');
                }
            } 
			else 
			{
                $paymentfee = new DataObject([
                            'code'  => 'paymentfee',
                            'value' => $this->getSource()->getMcPaymentfeeAmount(),
                            'base_value'=> $this->getSource()->getBaseMcPaymentfeeAmount(),
                            'label' => $this->getSource()->getMcPaymentfeeDescription()
                        ]);
                //Work out where to slot in paymentfee totals
                if ($parent->getTotal('shipping_incl')) {
                    $parent->addTotal($paymentfee,'shipping_incl');
                } elseif ($parent->getTotal('shipping')) {
                    $parent->addTotal($paymentfee,'shipping');
                } elseif ($parent->getTotal('subtotal_incl')) {
                    $parent->addTotal($paymentfee,'subtotal_incl');
                } else {
                    $parent->addTotal($paymentfee,'subtotal');
                }
            }
        }
        return $this;
    }
}