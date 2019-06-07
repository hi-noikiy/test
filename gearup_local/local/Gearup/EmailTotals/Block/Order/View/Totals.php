<?php
class Gearup_EmailTotals_Block_Order_View_Totals extends Mage_Sales_Block_Order_Totals
{
    public function getTotals($area=null)
    {
        $this->_totals = parent::getTotals($area);

        $inclLabel = ((float)$this->getOrder()->getTaxAmount())
            ? ' <span>(VAT Inclusive)</span>' : '';

        $this->_totals['grand_total']
            ->setLabel($this->__($this->_totals['grand_total']->getLabel() . $inclLabel));

        foreach ($this->_totals as $_code => $_total){
            ksort($this->_totals);
        }

        $grandTotal = $this->getTotal('grand_total');
        $taxTotal = $this->getTotal('tax');

        $this->removeTotal('grand_total');
        $this->removeTotal('tax');

        $this->_totals['grand_total'] = $grandTotal;
        $this->addTotal($grandTotal, 'last');

        if ($taxTotal) {
            $this->addTotal($taxTotal, 'last');
        }

        return $this->_totals;
    }
}