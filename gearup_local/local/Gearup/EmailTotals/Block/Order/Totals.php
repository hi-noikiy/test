<?php

/**
 * Class Gearup_EmailTotals_Block_Order_Totals
 */
class Gearup_EmailTotals_Block_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    /**
     * @param null $area
     * @return array
     */
    public function getTotals($area = null)
    {
        $this->_totals = parent::getTotals($area);

        $this->removeTotal('shipping');

        $order = $this->getOrder();
        $inclLabel = ((float)$order->getTaxAmount()) ? ' <span class="price-vat-label">(VAT Inclusive)</span>' : '';
        $this->_totals['grand_total']
            ->setLabel($this->__($this->_totals['grand_total']->getLabel() . $inclLabel));

        $grandTotal = $this->getTotal('grand_total');
        $this->removeTotal('grand_total');

        $this->addTotal($grandTotal, 'last');

        if ((float)$order->getTaxAmount()) {
            $taxAmountTotal = new Varien_Object(array(
                'code' => 'tax_amount',
                'field' => 'tax_amount',
                'strong' => true,
                'value' => $order->getTaxAmount(),
                'label' => $this->__('VAT Amount (%s)', Mage::getModel('tax/config')
                        ->customRateRequest($order->getShippingAddress()) . '%')
            ));

            $this->addTotal($taxAmountTotal, 'grand_total');
        }


        return $this->_totals;
    }
}