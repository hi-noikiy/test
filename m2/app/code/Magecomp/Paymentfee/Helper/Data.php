<?php
namespace Magecomp\Paymentfee\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Model\Config;

class Data extends AbstractHelper
{
    protected $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
        parent::__construct($context);
    }

    const XML_PATH_DISPLAY_CART_SURCHARGE = 'tax/cart_display/paymentfee';
    const XML_PATH_DISPLAY_SALES_SURCHARGE = 'tax/sales_display/paymentfee';
    const DEBUG = true;

    public function displayBothCart()
    {
        return ($this->scopeConfig->getValue(self::XML_PATH_DISPLAY_CART_SURCHARGE, ScopeInterface::SCOPE_STORE) == Config::DISPLAY_TYPE_BOTH);
    }

    public function displayIncludeTaxCart()
    {
        return ($this->scopeConfig->getValue(self::XML_PATH_DISPLAY_CART_SURCHARGE, ScopeInterface::SCOPE_STORE) == Config::DISPLAY_TYPE_INCLUDING_TAX);
    }

    public function displayBothSales()
    {
        return ($this->scopeConfig->getValue(self::XML_PATH_DISPLAY_SALES_SURCHARGE, ScopeInterface::SCOPE_STORE) == Config::DISPLAY_TYPE_BOTH);
    }

    public function displayIncludeTaxSales()
    {
        return ($this->scopeConfig->getValue(self::XML_PATH_DISPLAY_SALES_SURCHARGE, ScopeInterface::SCOPE_STORE) == Config::DISPLAY_TYPE_INCLUDING_TAX);
    }

    public function debug($mesg)
    {
        if (self::DEBUG) {
            $this->_logger->debug($mesg, null, 'paymentfee.log');
        }
    }
	
	public function getPaymentLabel()
	{
		$realtext = $this->scopeConfig->getValue('paymentfee/paymentfeepay/paydesc', ScopeInterface::SCOPE_STORE);
		if($realtext == '')
		{
			$realtext = 'Payment Paymentfee';
		}	
		return $realtext;
	}
}