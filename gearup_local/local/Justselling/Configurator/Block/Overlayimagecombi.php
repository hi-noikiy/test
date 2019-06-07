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
 
class Justselling_Configurator_Block_Overlayimagecombi extends Justselling_Configurator_Block_Default
{
	public function getImage(Justselling_Configurator_Model_Value $value) 
	{
		return $this->helper('configurator/image')->resize($value->getImage(),'image',$value->getImageSizeX(),$value->getImageSizeY());
	}
	
	public function getThumbnail(Justselling_Configurator_Model_Value $value) 
	{
		return $this->helper('configurator/image')->resize($value->getThumbnail(),'thumbnail',$value->getThumbnailSizeX(),$value->getThumbnailSizeY());
	}
	
	public function getThumbnailAlt(Justselling_Configurator_Model_Value $value) 
	{
		$file = $value->getThumbnailAlt();
		
		if( $file )
			return $this->helper('configurator/image')->resize($file,'thumbnail_alt',$value->getThumbnailSizeX(),$value->getThumbnailSizeY());
		else
			return 'thumbnail';
	}

    public function getValues() {
        $values =  $this->templateOption->values;

        $values = Mage::helper("configurator")->checkOptionValuesInStock($values);

        return $values;
    }
	
	public function isChecked(Justselling_Configurator_Model_Value $value) {
		return (bool) ( $value->getId() == $this->getSelectedValue() );
    }

    public function getCombinedElements()
    {
        $childrenStatus = $this->templateOption->getChildrenStatus();
        $elements = array();

        foreach ($childrenStatus as $status) {
            if( isset($status['is_combi']) && $status['is_combi'] ) {

                $elements[] = $status;
            }
        }

        $elements[] = array('id' => $this->getTemplateOption()->getId(), 'type'=>'overlaycombi');
        return $elements;
    }

    public function getSelectedValuePrice() {
        $value = $this->getSelectedValue();

        if($value) {
            $valueModel = Mage::getModel('configurator/value')->load($value);
            return $valueModel->getPrice();
        }

        return 0;
    }

    public function getValidationClasses()
    {
        $class = "";

        if( $this->templateOption->getIsRequire() ) {
            $class.= "required-entry ";
        }

        if( $this->templateOption->hasData('min_value') && $this->templateOption->getData('min_value') != $this->templateOption->getData('max_value') ) {
            //$class.="validate-test ";
            $class.= "validate-min-".$this->templateOption->getMinValue()." ";
        }

        if( $this->templateOption->hasData('max_value') && $this->templateOption->getData('min_value') != $this->templateOption->getData('max_value') ) {
            $class.= "validate-max-".$this->templateOption->getMaxValue()." ";
        }

        return $class;
    }

    public function getValidationMethods() {

        $script = "";

        if( $this->templateOption->hasData('min_value') ) {
            $minValue = $this->templateOption->getMinValue();
            $script.= "Validation.add('validate-min-$minValue','Eingabe muss min. $minValue sein',function(value,el){return ($minValue<=parseNumber(value));},{});";
        }

        if( $this->templateOption->hasData('max_value') ) {
            $maxValue = $this->templateOption->getMaxValue();
            $script.= "Validation.add('validate-max-$maxValue','Eingabe darf max. $maxValue sein',function(value,el){return ($maxValue>=parseNumber(value));},{});";
        }

        return $script;
    }

    public function getJavascript() {
        $ccClass = uniqid('cc');

        if( $this->templateOption->getOperator() == 'string' ) {
            $valueCallback = "function(self) { return Configuration.combinationValueCallbackString(self); }";
		} elseif( $this->templateOption->getOperator() == 'expression'){
			$valueCallback = "function(self) { return Configuration.combinationValueCallbackExpression(self, '".str_replace(array("\r\n", "\n", "\r"), ' ', str_replace("'", '"', $this->templateOption->getSelectcombiExpression()))."'," .$this->templateOption->getDecimalPlace() ."); }";
        } else {
            $valueCallback = "function (self) { return Configuration.combinationValueCallback(self, '".$this->templateOption->getOperator()."',".$this->templateOption->getDecimalPlace()."); }";
        }

        $decimal_place = $this->templateOption->getDecimalPlace();

        if( $this->templateOption->hasPricelist() ) {
            $pricelist = json_encode($this->templateOption->getValuePricelistDataAsArray($this->getOptionId()));
            $priceCallback = "function(self) { return Configuration.combinationPriceFromPricelistCallback(self, '".$pricelist."', ".$decimal_place.", 'overlaycombi'); }";
        } else {
			$pricelist = json_encode($this->getSelectcombiSimplePricelist());
            $operator = ($this->templateOption->getOperator() == 'string') ? '*'  : $this->templateOption->getOperator();
            $priceCallback = "function(self) { return Configuration.combinationPriceCallback(self,'".$pricelist."', '".$operator."', ".$decimal_place.",'overlaycombi'); }";
        }

        $js = "var $ccClass = new Product.TemplateOptions.Combination($valueCallback, $priceCallback, ".$this->templateOption->getId().", '".$this->getId()."', ".$this->getJsTemplateOption().");";

        foreach($this->getCombinedElements() as $element) {
            $id = 'options-'.$this->getProductOptionId().'-'.$this->getJsTemplateOption().'-template-'.$element['id'];
            $js.= "\nConfiguration.addCombinationHandler($ccClass, '$id', '".$element['id']."', '".$element['type']."', '".$pricelist."');";
        }
        return $js;
    }

	public function isCombi(){
		$isCombi = "false";
		if($this->getSelectedValue() && $this->templateOption->isCombi() || $this->templateOption->getOperator() == 'expression'){
			$isCombi = "true";
		}
		return $isCombi;
	}

}