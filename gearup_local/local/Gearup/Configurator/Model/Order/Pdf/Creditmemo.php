<?php

/**
 * Class Gearup_Configurator_Model_Order_Pdf_Creditmemo
 */
class Gearup_Configurator_Model_Order_Pdf_Creditmemo extends Mage_Sales_Model_Order_Pdf_Creditmemo
{
    /**
     * @param Mage_Sales_Model_Abstract $source
     * @return array
     */
    protected function _getTotalsList($source)
    {
        $salesHelper = Mage::helper('sales');
        $totals = parent::_getTotalsList($source);

        if (($shipTotalIndex = array_search('shipping_amount',
                array_column(array_column($totals, '_data'), 'source_field'))) !== false
        ) {
            unset($totals[$shipTotalIndex]);
        }
        foreach ($totals as $total) {
            if ($total->getSourceField() == 'shipping_amount') {
                $total->setAmount(0);
            }
            if ($total->getSourceField() == 'grand_total') {
                $total->setTitle($total->getTitle() . $salesHelper->__(' (VAT inclusive)'));
            }
            if ($total->getSourceField() == 'tax_amount') {
                $total->setSortOrder(1000);
            }
        }

        usort($totals, array($this, '_sortTotalsList'));
        return $totals;
    }
}