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
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Productattribute extends Justselling_Configurator_Block_Default
{	
    public function getProductAttributeValue() {
        $value = 0;
        $productId = null;

        $_product = $this->getProductOption()->getProduct();

        $attribute = $this->getOption()->getProductAttribute();
        if (preg_match("/([0-9]+)::([a-zA-Z0-9_\-]+)/", $attribute, $parts) == 1) {
            $productId = $parts[1];
            $attribute = $parts[2];
            $_product = Mage::getModel("catalog/product")->load($productId);
        }

        if ($_product->getResource()->getAttribute($attribute)) {
            $value = $_product->getResource()->getAttribute($attribute)->getFrontend()->getValue($_product);
        }elseif($valueFromData = $this->getMagicAttribute($_product, $attribute)){
			$value = $valueFromData;
		}

        return $value;
    }

    public function getAttribute() {
        return $this->getOption()->getProductAttribute();
    }

	public function isBlacklist(){
		$isBlacklist = 'false';
		if($this->getBlacklist() || $this->isCombinedAdaptSizeField()){
			$isBlacklist = 'true';
		}
		return $isBlacklist;
	}

	public function getMagicAttribute($_product, $attribute){
		$value = false;
		$getter = "get".ucfirst($attribute);

		try{
			$value = $_product->$getter();
		}catch (Exception $e){
			Mage::log('product attriubute from template throw an exception ', $e);
		}

		return $value;
	}
}