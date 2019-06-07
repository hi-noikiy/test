<?php

class Mish_Rounding_Model_Currency extends Mage_Directory_Model_Currency
{
    /**
     * Convert price to currency format
     *
     * @param   double $price
     * @param   string $toCurrency
     * @return  double
     */
    public function convert($price, $toCurrency = null)
    {
        if (is_null($toCurrency)) {
            return $price;
        }

        if ($rate = $this->getRate($toCurrency)) {
            $value = $price * $rate;

            return Mage::helper('rounding')->process($toCurrency, $value);
        }
    }
}