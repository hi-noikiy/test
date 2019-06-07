<?php
class Gearup_Configurator_Model_Order_Pdf_Order extends Fooman_EmailAttachments_Model_Order_Pdf_Order
{
    protected function _getTotalsList($source)
    {
        function a($var){
            return $var['source_field'] == 'tax_amount';
        }
        $totals = parent::_getTotalsList($source);

        $grandTotalIndex = array_search('grand_total', array_column(array_column($totals, '_data'), 'source_field'));
        $grandSortOrder = $totals[$grandTotalIndex]->getSortOrder();


        $inclLabel = ((float)Mage::getModel('sales/order')
            ->load(Mage::app()->getRequest()->getParam('order_id'))->getTaxAmount()) ? ' (VAT inclusive)' : '';
        $totals[$grandTotalIndex]->setTitle($totals[$grandTotalIndex]->getTitle() . Mage::helper('sales')->__($inclLabel));

        if (($taxTotalIndex = array_search('tax_amount', array_column(array_column($totals, '_data'), 'source_field'))) !== false) {
            $totals[$taxTotalIndex]->setSortOrder($grandSortOrder + 100);
        }
        if (($shipTotalIndex = array_search('shipping_amount', array_column(array_column($totals, '_data'), 'source_field'))) !== false) {
            unset($totals[$shipTotalIndex]);
        }
        usort($totals, array($this, '_sortTotalsList'));
        return $totals;
    }
}