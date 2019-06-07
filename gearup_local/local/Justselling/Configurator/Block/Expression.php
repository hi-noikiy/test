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
 * @copyright   Copyright ¬© 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Expression extends Justselling_Configurator_Block_Default
{	

	public function getCombinedElements()
	{
		$childrenStatus = $this->templateOption->getChildrenStatus();	
		$elements = array();
		
		foreach ($childrenStatus as $status) {
			if( isset($status['is_combi']) && $status['is_combi'] ) {
				
				$elements[] = $status;
			}
		}	
		
		return $elements;
	}
	
	public function getJavascript()
	{		
        $ccClass = uniqid('cc');
        $decimalPlace = $this->templateOption->getDecimalPlace();

        $valueCallback = "function(self) { return OptionExpression.getValue(self, '".str_replace(array("\r\n", "\n", "\r"), ' ', str_replace("'", '"', $this->templateOption->getExpression()))."',".$decimalPlace." ); }";

        $pricelist = json_encode($this->templateOption->getOptionPricelistDataAsArray($this->getOptionId()));
        $priceCallback = "function(self) { return Configuration.getPrice(self, '".$pricelist."', '".$this->templateOption->getOperatorValuePrice()."', ".$this->templateOption->getDecimalPlace()." ); }";

        $js = "var $ccClass = new Product.TemplateOptions.Combination($valueCallback,$priceCallback,".$this->templateOption->getId().",'".$this->getId()."',".$this->getJsTemplateOption().");";

        foreach($this->getCombinedElements() as $element) {
	        $id = 'options-'.$this->getProductOptionId().'-'.$this->getJsTemplateOption().'-template-'.$element['id'];
            $js.= "\nConfiguration.addCombinationHandler($ccClass, '$id', '".$element['id']."', '".$element['type']."', '".$pricelist."');";

	    }

		$js .= $ccClass.".calculate();";
		return $js;
	}

}

	
