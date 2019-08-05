<?php
namespace Magecomp\Paymentfee\Model\Sales\Pdf;

use Magecomp\Paymentfee\Helper\Data as PaymentfeeHelperData;
use Magento\Sales\Model\Order\Pdf\Total\DefaultTotal;
use Magento\Tax\Helper\Data as HelperData;
use Magento\Tax\Model\Calculation;
use Magento\Tax\Model\Config;
use Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory;

class Paymentfee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal {
    /**
     * @var Config
     */
    protected $_modelConfig;

    /**
     * @var PaymentfeeHelperData
     */
    protected $_helperData;

    public function __construct(HelperData $taxHelper, 
        Calculation $taxCalculation, 
        CollectionFactory $ordersFactory, 
        Config $modelConfig, 
        PaymentfeeHelperData $helperData, 
        array $data = [])
    {
        $this->_modelConfig = $modelConfig;
        $this->_helperData = $helperData;

        parent::__construct($taxHelper, $taxCalculation, $ordersFactory, $data);
    }


    public function getTotalsForDisplay()
    {
        $store = $this->getOrder()->getStore();
        $config= $this->_modelConfig;
        $amount = $this->getOrder()->formatPriceTxt($this->getSource()->getMcPaymentfeeAmount());
        $label = $this->getOrder()->getMcPaymentfeeDescription();
        $amountInclTax = $this->getSource()->getMcPaymentfeeAmount()+$this->getSource()->getMcPaymentfeeTaxAmount();
        $amountInclTax = $this->getOrder()->formatPriceTxt($amountInclTax);
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;

        if ($this->_helperData->displayBothSales()) {
            $totals = [
                [
                    'amount'    => $this->getAmountPrefix().$amount,
                    'label'     => $label . ':',
                    'font_size' => $fontSize
                ],
                [
                    'amount'    => $this->getAmountPrefix().$amountInclTax,
                    'label'     => $label . ':',
                    'font_size' => $fontSize
                ],
            ];
        } elseif ($this->_helperData->displayIncludeTaxSales()) {
            $totals = [[
                'amount'    => $this->getAmountPrefix().$amountInclTax,
                'label'     => $label . ':',
                'font_size' => $fontSize
            ]];
        } else {
            $totals = [[
                'amount'    => $this->getAmountPrefix().$amount,
                'label'     => $label . ':',
                'font_size' => $fontSize
            ]];
        }

        return $totals;
    }

    public function getAmount()
    {
        return $this->getSource()->getMcPaymentfeeAmount();
    }

}
