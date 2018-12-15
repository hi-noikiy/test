<?php

/**
 * @category    BusinessKing
 * @package     BusinessKing_PaymentCharge
 */
class Ktpl_Wholesaler_Block_Adminhtml_Sales_Totals extends Mage_Adminhtml_Block_Sales_Totals
{
    /**
     * Initialize order totals array
     *
     * @return Mage_Sales_Block_Order_Totals
     */
    protected function _initTotals()
    {
    	parent::_initTotals();
    	
        $source = $this->getSource();

        /**
         * Add store rewards
         */
        $totals = $this->_totals;
        $newTotals = array();
        if (count($totals)>0) {
        	foreach ($totals as $index=>$arr) {
        		if ($index == "grand_total") {
        			if (((float)$this->getSource()->getTierDiscount()) != 0) {
	        			$label = $this->__('Wholesaler Discount');
			            $newTotals['wholesaler_discount'] = new Varien_Object(array(
			                'code'  => 'wholesaler_discount',
			                'field' => 'tier_discount',
			                'value' => $source->getTierDiscount(),
			                'label' => $label
			            ));
        			}
        			if (((float)$this->getSource()->getPaymentCharge()) != 0) {
	        			$label = $this->__('Credit Card Charge');
			            $newTotals['payment_charge'] = new Varien_Object(array(
			                'code'  => 'payment_charge',
			                'field' => 'payment_charge',
			                'value' => $source->getPaymentCharge(),
			                'label' => $label
			            ));
        			}
        		}
        		$newTotals[$index] = $arr;
        	}
        	$this->_totals = $newTotals;
        }

        return $this;
    }
}
