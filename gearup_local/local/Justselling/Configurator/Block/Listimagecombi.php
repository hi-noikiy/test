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
 * @copyright   Copyright ÔøΩ 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Listimagecombi extends Justselling_Configurator_Block_Listimage
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

        $elements[] = array('id' => $this->getTemplateOption()->getId(), 'type'=>'listimagecombi');
        return $elements;
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
            $priceCallback = "function(self) { return Configuration.combinationPriceFromPricelistCallback(self, '".$pricelist."', ".$decimal_place.", 'listimagecombi'); }";
        } else {
			$pricelist = json_encode($this->getSelectcombiSimplePricelist());
            $operator = ($this->templateOption->getOperator() == 'string') ? '*'  : $this->templateOption->getOperator();
            $priceCallback = "function(self) { return Configuration.combinationPriceCallback(self,'".$pricelist."', '".$operator."', ".$decimal_place.",'listimagecombi'); }";
        }

        $js = "var $ccClass = new Product.TemplateOptions.Combination($valueCallback, $priceCallback, ".$this->templateOption->getId().", '".$this->getId()."', ".$this->getJsTemplateOption().");";

        foreach($this->getCombinedElements() as $element) {
            $id = 'options-'.$this->getProductOptionId().'-'.$this->getJsTemplateOption().'-template-'.$element['id'];
            $js.= "\nConfiguration.addCombinationHandler($ccClass, '$id', '".$element['id']."', '".$element['type']."', '".$pricelist."');";
        }
        return $js;
    }

	public function showLabel(){
		$showLabel = "false";
		if($this->getListimageStyle()){
			$showLabel = "true";
		}
		return $showLabel;
	}

	public function isCombi(){
		$isCombi = "false";
		if($this->getSelectedValue() && $this->templateOption->isCombi() || $this->templateOption->getOperator() == 'expression'){
			$isCombi = "true";
		}
		return $isCombi;
	}

	public function getImageSrc($value){
		$src = $this->getThumbnail($value);
		$color =  $this->getOptionValueColor($value);
		if(isset($color)  && !empty($color)){
			$width = $value['thumbnail_size_y'];
			$height = $value['thumbnail_size_y'];

			if($width && $height){
				$src = Mage::getBaseUrl() ."prodconf/combinedimage/getTransparentImage/width/".$width."/height/" .$height ."/color/" . str_replace("#","",$color);
			}
		}
		return $src;
	}

}
