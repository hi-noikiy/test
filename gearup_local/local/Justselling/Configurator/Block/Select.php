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
 
class Justselling_Configurator_Block_Select extends Justselling_Configurator_Block_Default
{	
	public function getValues() {
		$values =  $this->templateOption->values;

		$values = Mage::helper("configurator")->checkOptionValuesInStock($values);

		return $values;
	}	
	
	public function isChecked(Justselling_Configurator_Model_Value $value) {
        $value_id = $value->getId();
        $selected_id = $this->getSelectedValue();
		return (bool) ( $value_id  == $selected_id );
	}
	
	public function getImage(Justselling_Configurator_Model_Value $value) 
	{
		return $this->helper('configurator/image')->resize($value->getImage(),'image',$value->getImageSizeX(),$value->getImageSizeY());
	}


}