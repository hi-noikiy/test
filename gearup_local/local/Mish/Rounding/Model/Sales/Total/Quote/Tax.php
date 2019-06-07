<?php

class Mish_Rounding_Model_Sales_Total_Quote_Tax extends Mage_Tax_Model_Sales_Total_Quote_Tax {

    protected function _deltaRound($price, $rate, $direction, $type = 'regular') {
        if ($type == 'regular') {
            $price = ceil($price);
        }
        if ($price) {
            $rate = (string) $rate;
            $type = $type . $direction;
            // initialize the delta to a small number to avoid non-deterministic behavior with rounding of 0.5
            $delta = isset($this->_roundingDeltas[$type][$rate]) ? $this->_roundingDeltas[$type][$rate] : 0.000001;
            $price += $delta;
            $this->_roundingDeltas[$type][$rate] = $price - $this->_calculator->round($price);
            $price = $this->_calculator->round($price);
        }
        return $price;
    }

    /**
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param float $rate
     * @param array $appliedRates
     * @param string $taxId
     */
    protected function _calculateShippingTaxByRate(
    Mage_Sales_Model_Quote_Address $address, $rate, $appliedRates, $taxId = null) {
        $inclTax = $address->getIsShippingInclTax();
        $shipping = $address->getShippingTaxable();
        $baseShipping = $address->getBaseShippingTaxable();
        $rateKey = ($taxId == null) ? (string) $rate : $taxId;

        $hiddenTax = null;
        $baseHiddenTax = null;
        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $tax = $this->_calculator->calcTaxAmount($shipping, $rate, $inclTax, false);
                $baseTax = $this->_calculator->calcTaxAmount($baseShipping, $rate, $inclTax, false,true);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $address->getShippingDiscountAmount();
                $baseDiscountAmount = $address->getBaseShippingDiscountAmount();
                $tax = $this->_calculator->calcTaxAmount(
                        $shipping - $discountAmount, $rate, $inclTax, false
                );
                $baseTax = $this->_calculator->calcTaxAmount(
                        $baseShipping - $baseDiscountAmount, $rate, $inclTax, false,true
                );
                break;
        }

        if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
            $tax = $this->_deltaRound($tax, $rateKey, $inclTax);
            $baseTax = $this->_deltaRound($baseTax, $rateKey, $inclTax, 'base');
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
        } else {
            $tax = $this->_calculator->round($tax);
            $baseTax = $this->_calculator->round($baseTax);
            $this->_addAmount(max(0, $tax));
            $this->_addBaseAmount(max(0, $baseTax));
        }

        if ($inclTax && !empty($discountAmount)) {
            $taxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $shipping, $rate, $inclTax, false
            );
            $baseTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                    $baseShipping, $rate, $inclTax, false,true
            );
            if ($this->_config->getAlgorithm($this->_store) == Mage_Tax_Model_Calculation::CALC_TOTAL_BASE) {
                $taxBeforeDiscount = $this->_deltaRound($taxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount');
                $baseTaxBeforeDiscount = $this->_deltaRound($baseTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount_base');
            } else {
                $taxBeforeDiscount = $this->_calculator->round($taxBeforeDiscount);
                $baseTaxBeforeDiscount = $this->_calculator->round($baseTaxBeforeDiscount);
            }
            $hiddenTax = max(0, $taxBeforeDiscount - max(0, $tax));
            $baseHiddenTax = max(0, $baseTaxBeforeDiscount - max(0, $baseTax));
            $this->_hiddenTaxes[] = array(
                'rate_key' => $rateKey,
                'value' => $hiddenTax,
                'base_value' => $baseHiddenTax,
                'incl_tax' => $inclTax,
            );
        }

        $address->setShippingTaxAmount($address->getShippingTaxAmount() + max(0, $tax));
        $address->setBaseShippingTaxAmount($address->getBaseShippingTaxAmount() + max(0, $baseTax));
        $this->_saveAppliedTaxes($address, $appliedRates, $tax, $baseTax, $rate);
    }

    /**
     * Calculate unit tax anount based on unit price
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $rate
     * @param   array $taxGroups
     * @param   string $taxId
     * @param   boolean $recalculateRowTotalInclTax
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calcUnitTaxAmount(
    $item, $rate, &$taxGroups = null, $taxId = null, $recalculateRowTotalInclTax = false
    ) {
        $qty = $item->getTotalQty();
        $inclTax = $item->getIsPriceInclTax();
        $price = $item->getTaxableAmount();
        $basePrice = $item->getBaseTaxableAmount();
        $rateKey = ($taxId == null) ? (string) $rate : $taxId;

        $isWeeeEnabled = $this->_weeeHelper->isEnabled();
        $isWeeeTaxable = $this->_weeeHelper->isTaxable();

        $hiddenTax = null;
        $baseHiddenTax = null;
        $weeeTax = null;
        $baseWeeeTax = null;
        $unitTaxBeforeDiscount = null;
        $weeeTaxBeforeDiscount = null;
        $baseUnitTaxBeforeDiscount = null;
        $baseWeeeTaxBeforeDiscount = null;

        switch ($this->_config->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $unitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($price, $rate, $inclTax, false);
                $baseUnitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax, false,true);

                if ($isWeeeEnabled && $isWeeeTaxable) {
                    $weeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate, false);
                    $unitTaxBeforeDiscount += $weeeTaxBeforeDiscount;
                    $baseWeeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate);
                    $baseUnitTaxBeforeDiscount += $baseWeeeTaxBeforeDiscount;
                }
                $unitTaxBeforeDiscount = $unitTax = $this->_calculator->round($unitTaxBeforeDiscount);
                $baseUnitTaxBeforeDiscount = $baseUnitTax = $this->_calculator->round($baseUnitTaxBeforeDiscount);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $item->getDiscountAmount() / $qty;
                $baseDiscountAmount = $item->getBaseDiscountAmount() / $qty;

                //We want to remove weee
                if ($isWeeeEnabled && $this->_weeeHelper->includeInSubtotal()) {
                    $discountAmount = $discountAmount - $item->getWeeeDiscount() / $qty;
                    $baseDiscountAmount = $baseDiscountAmount - $item->getBaseWeeeDiscount() / $qty;
                }

                $unitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($price, $rate, $inclTax, false);
                $unitTaxDiscount = $this->_calculator->calcTaxAmount($discountAmount, $rate, $inclTax, false);
                $unitTax = $this->_calculator->round(max($unitTaxBeforeDiscount - $unitTaxDiscount, 0));

                $baseUnitTaxBeforeDiscount = $this->_calculator->calcTaxAmount($basePrice, $rate, $inclTax, false,true);
                $baseUnitTaxDiscount = $this->_calculator->calcTaxAmount($baseDiscountAmount, $rate, $inclTax, false,true);
                $baseUnitTax = $this->_calculator->round(max($baseUnitTaxBeforeDiscount - $baseUnitTaxDiscount, 0));

                if ($isWeeeEnabled && $this->_weeeHelper->isTaxable()) {
                    $weeeTax = $this->_calculateRowWeeeTax($item->getWeeeDiscount(), $item, $rate, false);
                    $weeeTax = $weeeTax / $qty;
                    $unitTax += $weeeTax;
                    $baseWeeeTax = $this->_calculateRowWeeeTax($item->getBaseWeeeDiscount(), $item, $rate);
                    $baseWeeeTax = $baseWeeeTax / $qty;
                    $baseUnitTax += $baseWeeeTax;
                }

                $unitTax = $this->_calculator->round($unitTax);
                $baseUnitTax = $this->_calculator->round($baseUnitTax);

                //Calculate the weee taxes before discount
                $weeeTaxBeforeDiscount = 0;
                $baseWeeeTaxBeforeDiscount = 0;

                if ($isWeeeTaxable) {
                    $weeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate, false);
                    $unitTaxBeforeDiscount += $weeeTaxBeforeDiscount;
                    $baseWeeeTaxBeforeDiscount = $this->_calculateWeeeTax(0, $item, $rate);
                    $baseUnitTaxBeforeDiscount += $baseWeeeTaxBeforeDiscount;
                }

                $unitTaxBeforeDiscount = max(0, $this->_calculator->round($unitTaxBeforeDiscount));
                $baseUnitTaxBeforeDiscount = max(0, $this->_calculator->round($baseUnitTaxBeforeDiscount));

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax = $unitTaxBeforeDiscount - $unitTax;
                    $baseHiddenTax = $baseUnitTaxBeforeDiscount - $baseUnitTax;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => $qty,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                } elseif ($discountAmount > $price) { // case with 100% discount on price incl. tax
                    $hiddenTax = $discountAmount - $price;
                    $baseHiddenTax = $baseDiscountAmount - $basePrice;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => $qty,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                }
                // calculate discount compensation
                // We need the discount compensation when dont calculate the hidden taxes
                // (when product does not include taxes)
                if (!$item->getNoDiscount() && $item->getWeeeTaxApplied()) {
                    $item->setDiscountTaxCompensation($item->getDiscountTaxCompensation() +
                            $unitTaxBeforeDiscount * $qty - max(0, $unitTax) * $qty);
                }
                break;
        }

        $rowTax = $this->_store->roundPrice(max(0, $qty * $unitTax));
        $baseRowTax = $this->_store->roundPrice(max(0, $qty * $baseUnitTax));
        $item->setTaxAmount($item->getTaxAmount() + $rowTax);
        $item->setBaseTaxAmount($item->getBaseTaxAmount() + $baseRowTax);
        if (is_array($taxGroups)) {
            $taxGroups[$rateKey]['tax'] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'] = max(0, $baseRowTax);
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($price * $qty);
                $item->setBaseRowTotalInclTax($basePrice * $qty);
            } else {
                $item->setRowTotalInclTax(
                        $item->getRowTotalInclTax() + ($unitTaxBeforeDiscount - $weeeTaxBeforeDiscount) * $qty);
                $item->setBaseRowTotalInclTax(
                        $item->getBaseRowTotalInclTax() +
                        ($baseUnitTaxBeforeDiscount - $baseWeeeTaxBeforeDiscount) * $qty);
            }
        }

        return $this;
    }

    /**
     * Calculate item tax amount based on row total
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $rate
     * @param   array $taxGroups
     * @param   string $taxId
     * @param   boolean $recalculateRowTotalInclTax
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _calcRowTaxAmount(
    $item, $rate, &$taxGroups = null, $taxId = null, $recalculateRowTotalInclTax = false
    ) {
        $inclTax = $item->getIsPriceInclTax();
        $subtotal = $taxSubtotal = $item->getTaxableAmount();
        $baseSubtotal = $baseTaxSubtotal = $item->getBaseTaxableAmount();
        $rateKey = ($taxId == null) ? (string) $rate : $taxId;

        $isWeeeEnabled = $this->_weeeHelper->isEnabled();
        $isWeeeTaxable = $this->_weeeHelper->isTaxable();

        $hiddenTax = null;
        $baseHiddenTax = null;
        $weeeTax = null;
        $baseWeeeTax = null;
        $rowTaxBeforeDiscount = null;
        $baseRowTaxBeforeDiscount = null;
        $weeeRowTaxBeforeDiscount = null;
        $baseWeeeRowTaxBeforeDiscount = null;

        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false,true);

                if ($isWeeeEnabled && $isWeeeTaxable) {
                    $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                    $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                    $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                    $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                }
                $rowTaxBeforeDiscount = $rowTax = $this->_calculator->round($rowTaxBeforeDiscount);
                $baseRowTaxBeforeDiscount = $baseRowTax = $this->_calculator->round($baseRowTaxBeforeDiscount);
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                $discountAmount = $item->getDiscountAmount();
                $baseDiscountAmount = $item->getBaseDiscountAmount();

                if ($isWeeeEnabled && $this->_weeeHelper->includeInSubtotal()) {
                    $discountAmount = $discountAmount - $item->getWeeeDiscount();
                    $baseDiscountAmount = $baseDiscountAmount - $item->getBaseWeeeDiscount();
                }

                $rowTax = $this->_calculator->calcTaxAmount(
                        max($subtotal - $discountAmount, 0), $rate, $inclTax
                );
                $baseRowTax = $this->_calculator->calcTaxAmount(
                        max($baseSubtotal - $baseDiscountAmount, 0), $rate, $inclTax,false,true
                );

                if ($isWeeeEnabled && $this->_weeeHelper->isTaxable()) {
                    $weeeTax = $this->_calculateRowWeeeTax($item->getWeeeDiscount(), $item, $rate, false);
                    $rowTax += $weeeTax;
                    $baseWeeeTax = $this->_calculateRowWeeeTax($item->getBaseWeeeDiscount(), $item, $rate);
                    $baseRowTax += $baseWeeeTax;
                }

                $rowTax = $this->_calculator->round($rowTax);
                $baseRowTax = $this->_calculator->round($baseRowTax);

                //Calculate the Row Tax before discount
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                        $subtotal, $rate, $inclTax, false
                );
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                        $baseSubtotal, $rate, $inclTax, false,true
                );

                //Calculate the Weee taxes before discount
                $weeeRowTaxBeforeDiscount = 0;
                $baseWeeeRowTaxBeforeDiscount = 0;
                if ($isWeeeTaxable) {
                    $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                    $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                    $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                    $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                }

                $rowTaxBeforeDiscount = max(0, $this->_calculator->round($rowTaxBeforeDiscount));
                $baseRowTaxBeforeDiscount = max(0, $this->_calculator->round($baseRowTaxBeforeDiscount));

                if ($inclTax && $discountAmount > 0) {
                    $hiddenTax = $rowTaxBeforeDiscount - $rowTax;
                    $baseHiddenTax = $baseRowTaxBeforeDiscount - $baseRowTax;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                } elseif ($discountAmount > $subtotal) { // case with 100% discount on price incl. tax
                    $hiddenTax = $discountAmount - $subtotal;
                    $baseHiddenTax = $baseDiscountAmount - $baseSubtotal;
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $hiddenTax,
                        'base_value' => $baseHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                }
                // calculate discount compensation
                if (!$item->getNoDiscount() && $item->getWeeeTaxApplied()) {
                    $item->setDiscountTaxCompensation($item->getDiscountTaxCompensation() +
                            $rowTaxBeforeDiscount - max(0, $rowTax));
                }
                break;
        }
        $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
        $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));
        if (is_array($taxGroups)) {
            $taxGroups[$rateKey]['tax'] = max(0, $rowTax);
            $taxGroups[$rateKey]['base_tax'] = max(0, $baseRowTax);
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($subtotal);
                $item->setBaseRowTotalInclTax($baseSubtotal);
            } else {
                $item->setRowTotalInclTax(
                        $item->getRowTotalInclTax() + $rowTaxBeforeDiscount - $weeeRowTaxBeforeDiscount);
                $item->setBaseRowTotalInclTax($item->getBaseRowTotalInclTax() +
                        $baseRowTaxBeforeDiscount - $baseWeeeRowTaxBeforeDiscount);
            }
        }
        return $this;
    }

    /**
     * Aggregate row totals per tax rate in array
     *
     * @param   Mage_Sales_Model_Quote_Item_Abstract $item
     * @param   float $rate
     * @param   array $taxGroups
     * @return  Mage_Tax_Model_Sales_Total_Quote
     */
    protected function _aggregateTaxPerRate(
    $item, $rate, &$taxGroups, $taxId = null, $recalculateRowTotalInclTax = false
    ) {
        $inclTax = $item->getIsPriceInclTax();
        $rateKey = ($taxId == null) ? (string) $rate : $taxId;
        $taxSubtotal = $subtotal = $item->getTaxableAmount();
        $baseTaxSubtotal = $baseSubtotal = $item->getBaseTaxableAmount();

        $isWeeeEnabled = $this->_weeeHelper->isEnabled();
        $isWeeeTaxable = $this->_weeeHelper->isTaxable();

        if (!isset($taxGroups[$rateKey]['totals'])) {
            $taxGroups[$rateKey]['totals'] = array();
            $taxGroups[$rateKey]['base_totals'] = array();
            $taxGroups[$rateKey]['weee_tax'] = array();
            $taxGroups[$rateKey]['base_weee_tax'] = array();
        }

        $hiddenTax = null;
        $baseHiddenTax = null;
        $weeeTax = null;
        $baseWeeeTax = null;
        $discount = 0;
        $rowTaxBeforeDiscount = 0;
        $baseRowTaxBeforeDiscount = 0;
        $weeeRowTaxBeforeDiscount = 0;
        $baseWeeeRowTaxBeforeDiscount = 0;


        switch ($this->_helper->getCalculationSequence($this->_store)) {
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_BEFORE_DISCOUNT_ON_INCL:
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($subtotal, $rate, $inclTax, false);
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount($baseSubtotal, $rate, $inclTax, false,true);

                if ($isWeeeEnabled && $isWeeeTaxable) {
                    $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                    $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                    $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                    $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                    $taxGroups[$rateKey]['weee_tax'][] = $this->_deltaRound($weeeRowTaxBeforeDiscount, $rateKey, $inclTax);
                    $taxGroups[$rateKey]['base_weee_tax'][] = $this->_deltaRound($baseWeeeRowTaxBeforeDiscount, $rateKey, $inclTax);
                }
                $taxBeforeDiscountRounded = $rowTax = $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax);
                $baseTaxBeforeDiscountRounded = $baseRowTax = $this->_deltaRound($baseRowTaxBeforeDiscount, $rateKey, $inclTax, 'base');
                $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
                $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));
                break;
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_EXCL:
            case Mage_Tax_Model_Calculation::CALC_TAX_AFTER_DISCOUNT_ON_INCL:
                if ($this->_helper->applyTaxOnOriginalPrice($this->_store)) {
                    $discount = $item->getOriginalDiscountAmount();
                    $baseDiscount = $item->getBaseOriginalDiscountAmount();
                } else {
                    $discount = $item->getDiscountAmount();
                    $baseDiscount = $item->getBaseDiscountAmount();
                }

                //We remove weee discount from discount if weee is not taxed
                if ($isWeeeEnabled && $this->_weeeHelper->includeInSubtotal()) {
                    $discount = $discount - $item->getWeeeDiscount();
                    $baseDiscount = $baseDiscount - $item->getBaseWeeeDiscount();
                }
                $taxSubtotal = max($subtotal - $discount, 0);
                $baseTaxSubtotal = max($baseSubtotal - $baseDiscount, 0);

                $rowTax = $this->_calculator->calcTaxAmount($taxSubtotal, $rate, $inclTax, false);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseTaxSubtotal, $rate, $inclTax, false,true);

                if ($isWeeeEnabled && $this->_weeeHelper->isTaxable()) {
                    $weeeTax = $this->_calculateRowWeeeTax($item->getWeeeDiscount(), $item, $rate, false);
                    $rowTax += $weeeTax;
                    $baseWeeeTax = $this->_calculateRowWeeeTax($item->getBaseWeeeDiscount(), $item, $rate);
                    $baseRowTax += $baseWeeeTax;
                    $taxGroups[$rateKey]['weee_tax'][] = $weeeTax;
                    $taxGroups[$rateKey]['base_weee_tax'][] = $baseWeeeTax;
                }

                $rowTax = $this->_deltaRound($rowTax, $rateKey, $inclTax);
                $baseRowTax = $this->_deltaRound($baseRowTax, $rateKey, $inclTax, 'base');

                $item->setTaxAmount($item->getTaxAmount() + max(0, $rowTax));
                $item->setBaseTaxAmount($item->getBaseTaxAmount() + max(0, $baseRowTax));

                //Calculate the Row taxes before discount
                $rowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                        $subtotal, $rate, $inclTax, false
                );
                $baseRowTaxBeforeDiscount = $this->_calculator->calcTaxAmount(
                        $baseSubtotal, $rate, $inclTax, false,true
                );


                if ($isWeeeTaxable) {
                    $weeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate, false);
                    $rowTaxBeforeDiscount += $weeeRowTaxBeforeDiscount;
                    $baseWeeeRowTaxBeforeDiscount = $this->_calculateRowWeeeTax(0, $item, $rate);
                    $baseRowTaxBeforeDiscount += $baseWeeeRowTaxBeforeDiscount;
                }

                $taxBeforeDiscountRounded = max(
                        0, $this->_deltaRound($rowTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount')
                );
                $baseTaxBeforeDiscountRounded = max(
                        0, $this->_deltaRound($baseRowTaxBeforeDiscount, $rateKey, $inclTax, 'tax_before_discount_base')
                );

                if (!$item->getNoDiscount()) {
                    if ($item->getWeeeTaxApplied()) {
                        $item->setDiscountTaxCompensation($item->getDiscountTaxCompensation() +
                                $taxBeforeDiscountRounded - max(0, $rowTax));
                    }
                }

                if ($inclTax && $discount > 0) {
                    $roundedHiddenTax = $taxBeforeDiscountRounded - max(0, $rowTax);
                    $baseRoundedHiddenTax = $baseTaxBeforeDiscountRounded - max(0, $baseRowTax);
                    $this->_hiddenTaxes[] = array(
                        'rate_key' => $rateKey,
                        'qty' => 1,
                        'item' => $item,
                        'value' => $roundedHiddenTax,
                        'base_value' => $baseRoundedHiddenTax,
                        'incl_tax' => $inclTax,
                    );
                }
                break;
        }

        $rowTotalInclTax = $item->getRowTotalInclTax();
        if (!isset($rowTotalInclTax) || $recalculateRowTotalInclTax) {
            if ($this->_config->priceIncludesTax($this->_store)) {
                $item->setRowTotalInclTax($subtotal);
                $item->setBaseRowTotalInclTax($baseSubtotal);
            } else {
                $item->setRowTotalInclTax(
                        $item->getRowTotalInclTax() + $taxBeforeDiscountRounded - $weeeRowTaxBeforeDiscount);
                $item->setBaseRowTotalInclTax(
                        $item->getBaseRowTotalInclTax() + $baseTaxBeforeDiscountRounded - $baseWeeeRowTaxBeforeDiscount);
            }
        }

        $taxGroups[$rateKey]['totals'][] = max(0, $taxSubtotal);
        $taxGroups[$rateKey]['base_totals'][] = max(0, $baseTaxSubtotal);
        $taxGroups[$rateKey]['tax'][] = max(0, $rowTax);
        $taxGroups[$rateKey]['base_tax'][] = max(0, $baseRowTax);
        return $this;
    }

}
