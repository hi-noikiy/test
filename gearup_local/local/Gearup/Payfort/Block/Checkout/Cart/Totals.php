<?php

/**
 * Class Gearup_Payfort_Block_Checkout_Cart_Totals
 */
class Gearup_Payfort_Block_Checkout_Cart_Totals extends Payfort_Pay_Block_Checkout_Cart_Totals
{
    /**
     * @return array|null
     */
    public function getTotals()
    {
        $taxRate = Mage::getModel('tax/config')->customRateRequest($this->getQuote()->getShippingAddress());
        $this->_totals = parent::getTotals();
        $taxTotal = isset($this->_totals['tax']) ? $this->_totals['tax'] : null;
        if ($taxTotal) {
            $taxTotal->setArea('footer')
                ->setTitle(Mage::helper('sales')->__('VAT Amount (%s)', $taxRate . '%'));
            unset($this->_totals['tax']);
            $this->_totals['tax'] = $taxTotal;
        }

        return $this->_totals;
    }
}