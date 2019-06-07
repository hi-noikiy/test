<?php

/**
 * Class Gearup_Checkout_Block_Checkout_Onepage_SuccessTotals
 */
class Gearup_Checkout_Block_Checkout_Onepage_SuccessTotals extends Mage_Sales_Block_Order_Totals
{
    /**
     * @param null $area
     * @return array
     */
    public function getTotals($area = null)
    {
        $this->_totals = parent::getTotals($area);

        $grandTotal = $this->_totals['grand_total'];

        $inclLabel = ((float)$this->getOrder()->getTaxAmount()) ? ' <span class="price-vat-label">(VAT Inclusive)</span>' : '';

        $grandTotal->setLabel($grandTotal->getLabel() . $inclLabel);


        $taxTotal = (isset($this->_totals['tax'])) ? $this->_totals['tax'] : null;

        $this->removeTotal('grand_total');
        $this->removeTotal('tax');
        $this->addTotal($grandTotal, 'last');

        if ($taxTotal) {
            $this->addTotal($taxTotal, 'grand_total');
        }

        return $this->_totals;
    }
}