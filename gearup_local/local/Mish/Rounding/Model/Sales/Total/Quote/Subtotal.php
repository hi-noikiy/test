<?php

class Mish_Rounding_Model_Sales_Total_Quote_Subtotal extends Mage_Tax_Model_Sales_Total_Quote_Subtotal {

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
     * Calculate item price and row total including/excluding tax based on total price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     *
     * @return Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _totalBaseCalculation($item, $request) {
        $calc = $this->_calculator;
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $calc->getRate($request);
        $qty = $item->getTotalQty();

        $price = $taxPrice = $this->_calculator->round($item->getCalculationPriceOriginal());
        $basePrice = $baseTaxPrice = $this->_calculator->round($item->getBaseCalculationPriceOriginal());
        $subtotal = $taxSubtotal = $this->_calculator->round($item->getRowTotal());
        $baseSubtotal = $baseTaxSubtotal = $this->_calculator->round($item->getBaseRowTotal());

        // if we have a custom price, determine if tax should be based on the original price
        $taxOnOrigPrice = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origSubtotal = $item->getOriginalPrice() * $qty;
            $baseOrigSubtotal = $item->getBaseOriginalPrice() * $qty;
        }

        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_sameRateAsStore($request)) {
                // determine which price to use when we calculate the tax
                if ($taxOnOrigPrice) {
                    $taxable = $origSubtotal;
                    $baseTaxable = $baseOrigSubtotal;
                } else {
                    $taxable = $subtotal;
                    $baseTaxable = $baseSubtotal;
                }
                $rowTaxExact = $calc->calcTaxAmount($taxable, $rate, true, false);
                $rowTax = $this->_deltaRound($rowTaxExact, $rate, true);
                $baseRowTaxExact = $calc->calcTaxAmount($baseTaxable, $rate, true, false, true);
                $baseRowTax = $this->_deltaRound($baseRowTaxExact, $rate, true, 'base');

                $taxPrice = $price;
                $baseTaxPrice = $basePrice;
                $taxSubtotal = $subtotal;
                $baseTaxSubtotal = $baseSubtotal;

                $subtotal = $subtotal - $rowTax;
                $baseSubtotal = $baseSubtotal - $baseRowTax;

                $price = $calc->round($subtotal / $qty);
                $basePrice = $calc->round($baseSubtotal / $qty);

                $isPriceInclTax = true;

                //Save the tax calculated
                $item->setRowTax($rowTax);
                $item->setBaseRowTax($baseRowTax);
            } else {
                $storeRate = $calc->getStoreRate($request, $this->_store);
                if ($taxOnOrigPrice) {
                    // the merchant already provided a customer's price that includes tax
                    $taxPrice = $price;
                    $baseTaxPrice = $basePrice;
                    // determine which price to use when we calculate the tax
                    $taxable = $this->_calculatePriceInclTax($item->getOriginalPrice(), $storeRate, $rate);
                    $baseTaxable = $this->_calculatePriceInclTax($item->getBaseOriginalPrice(), $storeRate, $rate);
                } else {
                    // determine the customer's price that includes tax
                    $taxPrice = $this->_calculatePriceInclTax($price, $storeRate, $rate);
                    $baseTaxPrice = $this->_calculatePriceInclTax($basePrice, $storeRate, $rate);
                    // determine which price to use when we calculate the tax
                    $taxable = $taxPrice;
                    $baseTaxable = $baseTaxPrice;
                }
                // determine the customer's tax amount based on the taxable price
                $tax = $this->_calculator->calcTaxAmount($taxable, $rate, true, true);
                $baseTax = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true, true);
                // determine the customer's price without taxes
                $price = $taxPrice - $tax;
                $basePrice = $baseTaxPrice - $baseTax;
                // determine subtotal amounts
                $taxable *= $qty;
                $baseTaxable *= $qty;
                $taxSubtotal = $taxPrice * $qty;
                $baseTaxSubtotal = $baseTaxPrice * $qty;
                $rowTax = $this->_deltaRound($calc->calcTaxAmount($taxable, $rate, true, false), $rate, true);
                $baseRowTax = $this->_deltaRound($calc->calcTaxAmount($baseTaxable, $rate, true, false, true), $rate, true, 'base');
                $subtotal = $taxSubtotal - $rowTax;
                $baseSubtotal = $baseTaxSubtotal - $baseRowTax;
                $isPriceInclTax = true;

                $item->setRowTax($rowTax);
                $item->setBaseRowTax($baseRowTax);
            }
        } else {
            // determine which price to use when we calculate the tax
            if ($taxOnOrigPrice) {
                $taxable = $origSubtotal;
                $baseTaxable = $baseOrigSubtotal;
            } else {
                $taxable = $subtotal;
                $baseTaxable = $baseSubtotal;
            }
            $appliedRates = $this->_calculator->getAppliedRates($request);
            $rowTaxes = array();
            $baseRowTaxes = array();
            foreach ($appliedRates as $appliedRate) {
                $taxId = $appliedRate['id'];
                $taxRate = $appliedRate['percent'];
                $rowTaxes[] = $this->_deltaRound($calc->calcTaxAmount($taxable, $taxRate, false, false), $taxId, false);
                $baseRowTaxes[] = $this->_deltaRound(
                        $calc->calcTaxAmount($baseTaxable, $taxRate, false, false, true), $taxId, false, 'base');
            }

            $taxSubtotal = $subtotal + array_sum($rowTaxes);
            $baseTaxSubtotal = $baseSubtotal + array_sum($baseRowTaxes);

            $taxPrice = $calc->round($taxSubtotal / $qty);
            $baseTaxPrice = $calc->round($baseTaxSubtotal / $qty);

            $isPriceInclTax = false;
        }

        if ($item->hasCustomPrice()) {
            /**
             * Initialize item original price before declaring custom price
             */
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        } else {
            $item->setConvertedPrice($price);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxSubtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseTaxSubtotal / $qty);
        } elseif ($isPriceInclTax) {
            $item->setDiscountCalculationPrice($subtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseSubtotal / $qty);
        }
        return $this;
    }

    protected function _unitBaseCalculation($item, $request) {
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $this->_calculator->getRate($request);
        $qty = $item->getTotalQty();

        $price = $taxPrice = $this->_calculator->round($item->getCalculationPriceOriginal());
        $basePrice = $baseTaxPrice = $this->_calculator->round($item->getBaseCalculationPriceOriginal());
        $subtotal = $taxSubtotal = $this->_calculator->round($item->getRowTotal());
        $baseSubtotal = $baseTaxSubtotal = $this->_calculator->round($item->getBaseRowTotal());

        // if we have a custom price, determine if tax should be based on the original price
        $taxOnOrigPrice = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origPrice = $item->getOriginalPrice();
            $baseOrigPrice = $item->getBaseOriginalPrice();
        }

        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_sameRateAsStore($request)) {
                // determine which price to use when we calculate the tax
                if ($taxOnOrigPrice) {
                    $taxable = $origPrice;
                    $baseTaxable = $baseOrigPrice;
                } else {
                    $taxable = $price;
                    $baseTaxable = $basePrice;
                }
                $tax = $this->_calculator->calcTaxAmount($taxable, $rate, true);
                $baseTax = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true, true);
                $taxPrice = $price;
                $baseTaxPrice = $basePrice;
                $taxSubtotal = $subtotal;
                $baseTaxSubtotal = $baseSubtotal;
                $price = $price - $tax;
                $basePrice = $basePrice - $baseTax;
                $subtotal = $price * $qty;
                $baseSubtotal = $basePrice * $qty;
                $isPriceInclTax = true;

                $item->setRowTax($tax * $qty);
                $item->setBaseRowTax($baseTax * $qty);
            } else {
                $storeRate = $this->_calculator->getStoreRate($request, $this->_store);
                if ($taxOnOrigPrice) {
                    // the merchant already provided a customer's price that includes tax
                    $taxPrice = $price;
                    $baseTaxPrice = $basePrice;
                    // determine which price to use when we calculate the tax
                    $taxable = $this->_calculatePriceInclTax($origPrice, $storeRate, $rate);
                    $baseTaxable = $this->_calculatePriceInclTax($baseOrigPrice, $storeRate, $rate);
                } else {
                    // determine the customer's price that includes tax
                    $taxPrice = $this->_calculatePriceInclTax($price, $storeRate, $rate);
                    $baseTaxPrice = $this->_calculatePriceInclTax($basePrice, $storeRate, $rate);
                    // determine which price to use when we calculate the tax
                    $taxable = $taxPrice;
                    $baseTaxable = $baseTaxPrice;
                }
                // determine the customer's tax amount
                $tax = $this->_calculator->calcTaxAmount($taxable, $rate, true, true);
                $baseTax = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true, true, true);
                // determine the customer's price without taxes
                $price = $taxPrice - $tax;
                $basePrice = $baseTaxPrice - $baseTax;
                // determine subtotal amounts
                $taxSubtotal = $taxPrice * $qty;
                $baseTaxSubtotal = $baseTaxPrice * $qty;
                $subtotal = $price * $qty;
                $baseSubtotal = $basePrice * $qty;
                $isPriceInclTax = true;

                $item->setRowTax($tax * $qty);
                $item->setBaseRowTax($baseTax * $qty);
            }
        } else {
            // determine which price to use when we calculate the tax
            if ($taxOnOrigPrice) {
                $taxable = $origPrice;
                $baseTaxable = $baseOrigPrice;
            } else {
                $taxable = $price;
                $baseTaxable = $basePrice;
            }
            $appliedRates = $this->_calculator->getAppliedRates($request);
            $taxes = array();
            $baseTaxes = array();
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $taxes[] = $this->_calculator->calcTaxAmount($taxable, $taxRate, false);
                $baseTaxes[] = $this->_calculator->calcTaxAmount($baseTaxable, $taxRate, false, false, true);
            }
            $tax = array_sum($taxes);
            $baseTax = array_sum($baseTaxes);
            $taxPrice = $price + $tax;
            $baseTaxPrice = $basePrice + $baseTax;
            $taxSubtotal = $taxPrice * $qty;
            $baseTaxSubtotal = $baseTaxPrice * $qty;
            $isPriceInclTax = false;
        }

        if ($item->hasCustomPrice()) {
            /**
             * Initialize item original price before declaring custom price
             */
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxPrice);
            $item->setBaseDiscountCalculationPrice($baseTaxPrice);
        }
        return $this;
    }

    /**
     * Calculate item price and row total including/excluding tax based on row total price rounding level
     *
     * @param Mage_Sales_Model_Quote_Item_Abstract $item
     * @param Varien_Object $request
     *
     * @return Mage_Tax_Model_Sales_Total_Quote_Subtotal
     */
    protected function _rowBaseCalculation($item, $request) {
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $this->_calculator->getRate($request);
        $qty = $item->getTotalQty();

        $price = $taxPrice = $this->_calculator->round($item->getCalculationPriceOriginal());
        $basePrice = $baseTaxPrice = $this->_calculator->round($item->getBaseCalculationPriceOriginal());
        $subtotal = $taxSubtotal = $this->_calculator->round($item->getRowTotal());
        $baseSubtotal = $baseTaxSubtotal = $this->_calculator->round($item->getBaseRowTotal());

        // if we have a custom price, determine if tax should be based on the original price
        $taxOnOrigPrice = !$this->_helper->applyTaxOnCustomPrice($this->_store) && $item->hasCustomPrice();
        if ($taxOnOrigPrice) {
            $origSubtotal = $item->getOriginalPrice() * $qty;
            $baseOrigSubtotal = $item->getBaseOriginalPrice() * $qty;
        }

        $item->setTaxPercent($rate);
        if ($this->_config->priceIncludesTax($this->_store)) {
            if ($this->_sameRateAsStore($request)) {
                // determine which price to use when we calculate the tax
                if ($taxOnOrigPrice) {
                    $taxable = $origSubtotal;
                    $baseTaxable = $baseOrigSubtotal;
                } else {
                    $taxable = $taxSubtotal;
                    $baseTaxable = $baseTaxSubtotal;
                }
                $rowTax = $this->_calculator->calcTaxAmount($taxable, $rate, true, true);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true, true);
                $taxPrice = $price;
                $baseTaxPrice = $basePrice;
                $taxSubtotal = $subtotal;
                $baseTaxSubtotal = $baseSubtotal;
                $subtotal = $this->_calculator->round($subtotal - $rowTax);
                $baseSubtotal = $this->_calculator->round($baseSubtotal - $baseRowTax);
                $price = $this->_calculator->round($subtotal / $qty);
                $basePrice = $this->_calculator->round($baseSubtotal / $qty);
                $isPriceInclTax = true;

                $item->setRowTax($rowTax);
                $item->setBaseRowTax($baseRowTax);
            } else {
                $storeRate = $this->_calculator->getStoreRate($request, $this->_store);
                if ($taxOnOrigPrice) {
                    // the merchant already provided a customer's price that includes tax
                    $taxPrice = $price;
                    $baseTaxPrice = $basePrice;
                    // determine which price to use when we calculate the tax
                    $taxable = $this->_calculatePriceInclTax($item->getOriginalPrice(), $storeRate, $rate);
                    $baseTaxable = $this->_calculatePriceInclTax($item->getBaseOriginalPrice(), $storeRate, $rate);
                } else {
                    // determine the customer's price that includes tax
                    $taxPrice = $this->_calculatePriceInclTax($price, $storeRate, $rate);
                    $baseTaxPrice = $this->_calculatePriceInclTax($basePrice, $storeRate, $rate);
                    // determine which price to use when we calculate the tax
                    $taxable = $taxPrice;
                    $baseTaxable = $baseTaxPrice;
                }
                // determine the customer's tax amount
                $tax = $this->_calculator->calcTaxAmount($taxable, $rate, true, true);
                $baseTax = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true, true);
                // determine the customer's price without taxes
                $price = $taxPrice - $tax;
                $basePrice = $baseTaxPrice - $baseTax;
                // determine subtotal amounts
                $taxable *= $qty;
                $baseTaxable *= $qty;
                $taxSubtotal = $taxPrice * $qty;
                $baseTaxSubtotal = $baseTaxPrice * $qty;
                $rowTax = $this->_calculator->calcTaxAmount($taxable, $rate, true, true);
                $baseRowTax = $this->_calculator->calcTaxAmount($baseTaxable, $rate, true, true, true);
                $subtotal = $taxSubtotal - $rowTax;
                $baseSubtotal = $baseTaxSubtotal - $baseRowTax;
                $isPriceInclTax = true;

                $item->setRowTax($rowTax);
                $item->setBaseRowTax($baseRowTax);
            }
        } else {
            // determine which price to use when we calculate the tax
            if ($taxOnOrigPrice) {
                $taxable = $origSubtotal;
                $baseTaxable = $baseOrigSubtotal;
            } else {
                $taxable = $subtotal;
                $baseTaxable = $baseSubtotal;
            }

            $appliedRates = $this->_calculator->getAppliedRates($request);
            $rowTaxes = array();
            $baseRowTaxes = array();
            foreach ($appliedRates as $appliedRate) {
                $taxRate = $appliedRate['percent'];
                $rowTaxes[] = $this->_calculator->calcTaxAmount($taxable, $taxRate, false, true);
                $baseRowTaxes[] = $this->_calculator->calcTaxAmount($baseTaxable, $taxRate, false, true, true);
            }
            $rowTax = array_sum($rowTaxes);
            $baseRowTax = array_sum($baseRowTaxes);
            $taxSubtotal = $subtotal + $rowTax;
            $baseTaxSubtotal = $baseSubtotal + $baseRowTax;
            $taxPrice = $this->_calculator->round($taxSubtotal / $qty);
            $baseTaxPrice = $this->_calculator->round($baseTaxSubtotal / $qty);
            $isPriceInclTax = false;
        }

        if ($item->hasCustomPrice()) {
            /**
             * Initialize item original price before declaring custom price
             */
            $item->getOriginalPrice();
            $item->setCustomPrice($price);
            $item->setBaseCustomPrice($basePrice);
        }
        $item->setPrice($basePrice);
        $item->setBasePrice($basePrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        $item->setPriceInclTax($taxPrice);
        $item->setBasePriceInclTax($baseTaxPrice);
        $item->setRowTotalInclTax($taxSubtotal);
        $item->setBaseRowTotalInclTax($baseTaxSubtotal);
        $item->setTaxableAmount($taxable);
        $item->setBaseTaxableAmount($baseTaxable);
        $item->setIsPriceInclTax($isPriceInclTax);
        if ($this->_config->discountTax($this->_store)) {
            $item->setDiscountCalculationPrice($taxSubtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseTaxSubtotal / $qty);
        } elseif ($isPriceInclTax) {
            $item->setDiscountCalculationPrice($subtotal / $qty);
            $item->setBaseDiscountCalculationPrice($baseSubtotal / $qty);
        }

        return $this;
    }

    protected function _recollectItem($address, Mage_Sales_Model_Quote_Item_Abstract $item) {
        $store = $address->getQuote()->getStore();
        $request = $this->_getStoreTaxRequest($address);
        $request->setProductClassId($item->getProduct()->getTaxClassId());
        $rate = $this->_calculator->getRate($request);
        $qty = $item->getTotalQty();

        $price = $taxPrice = $item->getCalculationPriceOriginal();
        $basePrice = $baseTaxPrice = $item->getBaseCalculationPriceOriginal();
        $subtotal = $taxSubtotal = $item->getRowTotal();
        $baseSubtotal = $baseTaxSubtotal = $item->getBaseRowTotal();

        if ($this->_config->discountTax($store)) {
            $item->setDiscountCalculationPrice($price);
            $item->setBaseDiscountCalculationPrice($basePrice);
        }

        /**
         * Use original price for tax calculation
         */
        if ($item->hasCustomPrice() && !$this->_helper->applyTaxOnCustomPrice($store)) {
            $taxPrice = $item->getOriginalPrice();
            $baseTaxPrice = $item->getBaseOriginalPrice();
            $taxSubtotal = $taxPrice * $qty;
            $baseTaxSubtotal = $baseTaxPrice * $qty;
        }

        if ($this->_areTaxRequestsSimilar) {
            $item->setRowTotalInclTax($subtotal);
            $item->setBaseRowTotalInclTax($baseSubtotal);
            $item->setPriceInclTax($price);
            $item->setBasePriceInclTax($basePrice);

            $item->setTaxCalcPrice($taxPrice);
            $item->setBaseTaxCalcPrice($baseTaxPrice);
            $item->setTaxCalcRowTotal($taxSubtotal);
            $item->setBaseTaxCalcRowTotal($baseTaxSubtotal);
        }

        $this->_subtotalInclTax += $subtotal;
        $this->_baseSubtotalInclTax += $baseSubtotal;

        if ($this->_config->getAlgorithm($store) == Mage_Tax_Model_Calculation::CALC_UNIT_BASE) {
            $taxAmount = $this->_calculator->calcTaxAmount($taxPrice, $rate, true);
            $baseTaxAmount = $this->_calculator->calcTaxAmount($baseTaxPrice, $rate, true, false, true);
            $unitPrice = $this->_calculator->round($price - $taxAmount);
            $baseUnitPrice = $this->_calculator->round($basePrice - $baseTaxAmount);
            $subtotal = $this->_calculator->round($unitPrice * $qty);
            $baseSubtotal = $this->_calculator->round($baseUnitPrice * $qty);
        } else {
            $taxAmount = $this->_calculator->calcTaxAmount($taxSubtotal, $rate, true, false);
            $baseTaxAmount = $this->_calculator->calcTaxAmount($baseTaxSubtotal, $rate, true, false, true);
            $unitPrice = ($subtotal - $taxAmount) / $qty;
            $baseUnitPrice = ($baseSubtotal - $baseTaxAmount) / $qty;
            $subtotal = $this->_calculator->round(($subtotal - $taxAmount));
            $baseSubtotal = $this->_calculator->round(($baseSubtotal - $baseTaxAmount));
        }

        if ($item->hasCustomPrice()) {
            $item->setCustomPrice($unitPrice);
            $item->setBaseCustomPrice($baseUnitPrice);
        }
        $item->setPrice($baseUnitPrice);
        $item->setOriginalPrice($unitPrice);
        $item->setBasePrice($baseUnitPrice);
        $item->setRowTotal($subtotal);
        $item->setBaseRowTotal($baseSubtotal);
        return $this;
    }

}
