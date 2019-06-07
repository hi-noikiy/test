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
* @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
* @license     http://www.justselling.de/lizenz
**/

class Justselling_Configurator_Block_Matrixvalue extends Justselling_Configurator_Block_Default
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

	public function getMatrix() {
		$matrix = Mage::getModel("configurator/optionmatrix")->loadByOptionId($this->getTemplateOption()->getId());
		return $matrix->getMatrix();
	}

	public function getScaleX() {
		$matrix = json_decode($this->getMatrix());
		if (!$matrix) return null;
		$scale_x = array();
		foreach ($matrix as $key => $value) {
			$scale_x[] = $key;
		}
		return json_encode($scale_x);
	}
	
	public function getScaleY() {
		$matrix = json_decode($this->getMatrix());
		if (!$matrix) return null;
		$scale_y = array();
		foreach ($matrix as $key => $value) {
			foreach ($value as $key => $value1) {
				$scale_y[] = $key;
			}
			break;
		}
		return json_encode($scale_y);	
	}
		
	public function getJavascript()
	{
		$ccClass = uniqid('cc');
        $valueCallback = "function(self) { return OptionMatrixvalue.getValue(self, '".$this->getTemplateOption()->getMatrixDimensionX()."','".$this->getScaleX()."','".$this->getTemplateOption()->getMatrixOperatorX()."', '".$this->getTemplateOption()->getMatrixDimensionY()."','".$this->getScaleY()."','".$this->getTemplateOption()->getMatrixOperatorY()."','".$this->getMatrix()."'); }";

        $pricelist = json_encode($this->templateOption->getOptionPricelistDataAsArray($this->getOptionId()));
        $priceCallback = "function(self) { return Configuration.getPrice(self, '".$pricelist."', '".$this->templateOption->getOperatorValuePrice()."', ".$this->templateOption->getDecimalPlace()." ); }";

        $js = "var $ccClass = new Product.TemplateOptions.Combination($valueCallback,$priceCallback,".$this->templateOption->getId().",'".$this->getId()."',".$this->getJsTemplateOption().");";

        foreach($this->getCombinedElements() as $element) {
            $id = 'options-'.$this->getProductOptionId().'-'.$this->getJsTemplateOption().'-template-'.$element['id'];
            $js.= "\nConfiguration.addCombinationHandler($ccClass, '$id', '".$element['id']."', '".$element['type']."', '".$pricelist."');";

        }
        return $js;
	}

}

	
