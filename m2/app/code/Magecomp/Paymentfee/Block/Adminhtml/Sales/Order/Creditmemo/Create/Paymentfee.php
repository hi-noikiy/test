<?php
namespace Magecomp\Paymentfee\Block\Adminhtml\Sales\Order\Creditmemo\Create;

use Magecomp\Paymentfee\Helper\Data as HelperData;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Block\Adminhtml\Order\Creditmemo\Totals;
use Magento\Sales\Helper\Admin;

class Paymentfee extends Totals {
    /**
     * @var HelperData
     */
    protected $_helperData;

    public function __construct(Context $context, 
        Registry $registry, 
        Admin $adminHelper, 
        HelperData $helperData, 
        array $data = [])
    {
        $this->_helperData = $helperData;

        parent::__construct($context, $registry, $adminHelper, $data);
    }


    protected $_paymentfeeAmount = '';
    protected $_paymentfeeDescription = '';

    public function initTotals() {

        if ($this->getSource()->getMcPaymentfeeAmount()!= 0){
            $parent = $this->getParentBlock();
            if ($this->_helperData->displayIncludeTaxSales()) {
                $this->_paymentfeeAmount=$this->getSource()->getMcPaymentfeeAmount() + $this->getSource()->getMcPaymentfeeTaxAmount();
                $this->_paymentfeeDescription = __('%1 (Incl. Tax)', $this->getSource()->getOrder()->getMcPaymentfeeDescription());
            } else {
                $this->_paymentfeeAmount=$this->getSource()->getMcPaymentfeeAmount();
                $this->_paymentfeeDescription = __('%1 (Excl. Tax)', $this->getSource()->getOrder()->getMcPaymentfeeDescription());
            }
            $paymentfee = new DataObject([
                    'block_name'=> $this->getNameInLayout(),
                    'code'  => 'paymentfee'
                ]);

            //Work out where to slot in paymentfee totals
            if ($parent->getTotal('discount')) {
                $parent->addTotal($paymentfee,'discount');
            } elseif ($parent->getTotal('shipping_incl')) {
                $parent->addTotal($paymentfee,'shipping_incl');
            } elseif ($parent->getTotal('shipping')) {
                $parent->addTotal($paymentfee,'shipping');
            } elseif ($parent->getTotal('subtotal_incl')) {
                $parent->addTotal($paymentfee,'subtotal_incl');
            } else {
                $parent->addTotal($paymentfee,'subtotal');
            }

            
        }
        return $this;
    }


    public function getPaymentfeeAmount()
    {
        return $this->_paymentfeeAmount;
    }
    
    public function getPaymentfeeDescription()
    {
        return $this->_paymentfeeDescription;
    }

}