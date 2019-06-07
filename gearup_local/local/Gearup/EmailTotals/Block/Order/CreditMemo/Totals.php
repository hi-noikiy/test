<?php
class Gearup_EmailTotals_Block_Order_CreditMemo_Totals extends Mage_Sales_Block_Order_Creditmemo_Totals
{
    public function getTotals($area = null)
    {
        $this->_totals = parent::getTotals($area);
        $grandTotal = $this->getTotal('grand_total');
        $this->removeTotal('grand_total');
        $this->addTotalBefore($grandTotal, 'tax');

        $inclLabel = ((float)$this->getOrder()->getTaxAmount())
            ? ' <span class="price-vat-label">(VAT Inclusive)</span>' : '';
        $this->_totals['grand_total']
            ->setLabel($this->__($this->_totals['grand_total']->getLabel() . $inclLabel));

        return $this->_totals;
    }
}