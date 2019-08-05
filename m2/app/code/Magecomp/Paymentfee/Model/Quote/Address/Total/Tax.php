<?php
namespace Magecomp\Paymentfee\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote\Address as QuoteAddress;
use Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector as TotalTax;

class Tax extends TotalTax {

	public function _saveAppliedTaxes(QuoteAddress\Total $addresstotal, $applied, $amount, $baseAmount, $rate) 
	{
        return parent::_saveAppliedTaxes($addresstotal, $applied, $amount, $baseAmount, $rate);
    }
}
