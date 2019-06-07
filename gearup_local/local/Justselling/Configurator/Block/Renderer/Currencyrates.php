<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 *
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 *
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright © 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Block_Renderer_Currencyrates extends Mage_Core_Block_Template
{
        public function getRates() {

            $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
            $allowedCurrencies = Mage::getModel('directory/currency')
                ->getConfigAllowCurrencies();
            $currencyRates = Mage::getModel('directory/currency')
                ->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));

            return json_encode($currencyRates);
        }
}