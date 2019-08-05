<?php
namespace Magecomp\Paymentfee\Block\Adminhtml\Sales\Order\Create\Totals;

use Magecomp\Paymentfee\Helper\Data as PaymentfeeHelperData;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Totals\DefaultTotals;
use Magento\Sales\Helper\Data as HelperData;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Sales\Model\Config;

class Paymentfee extends DefaultTotals {
    /**
     * @var PaymentfeeHelperData
     */
    protected $_helperData;

    public function __construct(Context $context, 
        Quote $sessionQuote, 
        Create $orderCreate, 
        PriceCurrencyInterface $priceCurrency, 
        HelperData $salesData, 
        Config $salesConfig, 
        PaymentfeeHelperData $helperData, 
        array $data = [])
    {
        $this->_helperData = $helperData;

        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesData, $salesConfig, $data);
    }

    public function getPaymentfeeExcludeTax()
    {
        return $this->getTotal()->getAddress()->getMcPaymentfeeAmount();
    }

    public function getPaymentfeeIncludeTax()
    {
        return $this->getTotal()->getAddress()->getMcPaymentfeeAmount()+ $this->getTotal()->getAddress()->getMcPaymentfeeTaxAmount();
    }

    public function getIncludeTaxLabel()
    {
        return $this->_helperData->__('%1 (Incl. Tax)', $this->getTotal()->getAddress()->getMcPaymentfeeDescription());
    }

    public function getExcludeTaxLabel()
    {
        return $this->_helperData->__('%1 (Excl. Tax)', $this->getTotal()->getAddress()->getMcPaymentfeeDescription());
    }

    public function displayBoth()
    {
        return $this->_helperData->displayBothCart();
    }

    public function displayIncludeTax()
    {
        return $this->_helperData->displayIncludeTaxCart();
    }

}