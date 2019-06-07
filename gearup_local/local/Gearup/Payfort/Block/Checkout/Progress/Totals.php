<?php

/**
 * Class Gearup_Payfort_Block_Checkout_Progress_Totals
 */
class Gearup_Payfort_Block_Checkout_Progress_Totals extends Gearup_Payfort_Block_Checkout_Cart_Totals
{
    /**
     * @return array
     */
    public function getTotals()
    {
        $this->_totals = parent::getTotals();
        $totals [] = $this->_totals['grand_total'];
        if ($this->_totals['tax']) {
            $totals [] = $this->_totals['tax'];
        }

        return $totals;
    }
}