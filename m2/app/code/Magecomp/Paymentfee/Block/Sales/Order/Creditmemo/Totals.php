<?php
namespace Magecomp\Paymentfee\Block\Sales\Order\Creditmemo;

use Magecomp\Paymentfee\Helper\Data as HelperData;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Order\Creditmemo\Totals as CreditmemoTotals;

class Totals extends CreditmemoTotals {
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


    public function initTotals() {
        if ($this->getSource()->getOrder()->getMcPaymentfeeAmount()!= 0){
            $parent = $this->getParentBlock();
            if ($this->_helperData->displayBothSales()) {
                $paymentfee = new DataObject([
                            'code'  => 'paymentfee',
                            'value' => $this->getSource()->getOrder()->getMcPaymentfeeAmount(),
                            'base_value'=> $this->getSource()->getOrder()->getBaseMcPaymentfeeAmount(),
                            'label' => __('%1 (Excl. Tax)', $this->getSource()->getOrder()->getMcPaymentfeeDescription())
                        ]);
                $paymentfeeIncl = new DataObject([
                            'code'  => 'paymentfee_incl',
                            'value' => $this->getSource()->getOrder()->getMcPaymentfeeAmount() + $this->getSource()->getOrder()->getMcPaymentfeeTaxAmount(),
                            'base_value'=> $this->getSource()->getOrder()->getBaseMcPaymentfeeAmount()+ $this->getSource()->getOrder()->getBaseMcPaymentfeeTaxAmount(),
                            'label' => __('%1 (Incl. Tax)', $this->getSource()->getOrder()->getMcPaymentfeeDescription())
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
            } elseif ($this->_helperData->displayIncludeTaxSales()) {
                $paymentfeeIncl = new DataObject([
                            'code'  => 'paymentfee_incl',
                            'value' => $this->getSource()->getOrder()->getMcPaymentfeeAmount() + $this->getSource()->getOrder()->getMcPaymentfeeTaxAmount(),
                            'base_value'=> $this->getSource()->getOrder()->getBaseMcPaymentfeeAmount()+ $this->getSource()->getOrder()->getBaseMcPaymentfeeTaxAmount(),
                            'label' => $this->getSource()->getOrder()->getMcPaymentfeeDescription()
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
            } else {
                $paymentfee = new DataObject([
                            'code'  => 'paymentfee',
                            'value' => $this->getSource()->getOrder()->getMcPaymentfeeAmount(),
                            'base_value'=> $this->getSource()->getOrder()->getBaseMcPaymentfeeAmount(),
                            'label' => $this->getSource()->getOrder()->getMcPaymentfeeDescription()
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