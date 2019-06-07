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
 
class Justselling_Configurator_Block_Text extends Justselling_Configurator_Block_Default
{						
	public function getValidationMethods() {
		
		$script = "";
		
		if( $this->getMinValue() != NULL ) {
			
			$minValue = $this->getMinValue();	
			$script.= "Validation.add('validate-min-$minValue','".$this->helper('configurator')->__('Input must be at least %s', $minValue)."',function(value,el){return ($minValue<=parseNumber(value));},{});";
		}
		
		if( $this->getMaxValue() ) {
			$maxValue = $this->getMaxValue();
			$script.= "Validation.add('validate-max-$maxValue','".$this->helper('configurator')->__('Input must be less than %s', $maxValue)."',function(value,el){return ($maxValue>=parseNumber(value));},{});";
		}	
		
		return $script;
	}
	
	//public function get
	
	public function getMaxCharacters() {
		return $this->templateOption->getMaxCharacters();
	}
	
	public function getMinValue() {
		return $this->templateOption->getMinValue();
	}
	
	public function getMaxValue() {
		return $this->templateOption->getMaxValue();
	}	

	public function getTextValidate() {
		return $this->templateOption->getTextValidate();
	}
		
	public function getPrice($price=NULL, $qty=0) {
		if (!$price) {
			$price = $this->templateOption->getPrice();
		}

		$templateErpOn = $this->getTemplateModel()->getAltCheckout();
		if($templateErpOn && is_numeric($qty) && $qty > 0){
			$price = $price * $qty;
		}
		return $price;
	}

	public function isBlacklist(){
		$isBlacklist = 'false';
		if($this->getBlacklist() || $this->getPrice() > 0 || $this->isCombinedAdaptSizeField()){
			$isBlacklist = 'true';
		}
		return $isBlacklist;
	}
}