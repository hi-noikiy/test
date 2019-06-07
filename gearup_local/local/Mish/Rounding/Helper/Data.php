<?php
/**
 * Rounding Helper
 */

class Mish_Rounding_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Process rounding
     * @param Mage_Directory_Model_Currency $currency
     * @param $value
     * @return float
     */
    public function process(Mage_Directory_Model_Currency $currency, $value)
    {
        $code = $currency->getCurrencyCode();
        $value = (double) $value;

        if (in_array($code, array('AED','SAR'))) {
            if ($value < 0) {
                $value = floor($value);
            }
            $value = ceil($value);
        } elseif (in_array($code, array('OMR', 'KWD', 'BHD', 'QAR'))) {
            $value = round($value, 1);
        }
        return $value;
    }
    
    public function getRoundedTaxShipment($price, $shipAddr = null, $format = true){
        $rate  = Mage::getModel('tax/config')->customRateRequest($shipAddr);
        $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();
        $currencyObj = new Mage_Directory_Model_Currency;
        $currencyObj->setCurrencyCode($currentCurrencyCode);
        $helper = Mage::helper('directory');
        $conShipPrice = $helper->currencyConvert($price,'USD',$currencyObj);
        $shippingPrice  = $conShipPrice + ($conShipPrice * $rate  / 100);
        $amount = Mage::helper('rounding')->process($currencyObj,$shippingPrice);

        if ($format) {
            $amount = Mage::app()->getLocale()->currency($currentCurrencyCode)->toCurrency($amount);
        }



        return $amount;
    }
} 