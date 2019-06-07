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
 * @copyright   Copyright � 2013 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Checkbox extends Justselling_Configurator_Block_Default
{	
	public function getValues() {
		return $this->templateOption->values;
	}	
	
	public function isChecked() {
        $isChecked = false;

        $isInEditMode = Mage::registry('isInEditMode');
        if($isInEditMode){
            $options = $this->getSelectedTemplateOptions();
            if($this->getValue() == $options[$this->templateOption->getId()]){
                $isChecked = true;
            }
        }else{
            if($this->getSelectedValue()){
                $isChecked = true;
            }
        }
		return $isChecked;
	}
	
}