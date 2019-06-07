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
 
class Justselling_Configurator_Block_Adminhtml_Font_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		
		$this->_objectId = "id";
		$this->_blockGroup = "configurator";
		$this->_controller = "adminhtml_font";
		
		$this->_updateButton("save", "label", Mage::helper("configurator")->__("Save Font") );
		$this->_updateButton("delete", "label", Mage::helper("configurator")->__("Delete Font") );
	}
	
	protected function _prepareLayout()
	{
		
		
		return parent::_prepareLayout();
	}
	
	public function getHeaderText()
	{
		if( Mage::registry("font_data") && Mage::registry("font_data")->getId() )
		{
			return Mage::helper("configurator")->__("Edit Font '%s'",$this->htmlEscape(Mage::registry("font_data")->getId()) );
		}
		else
		{
			return Mage::helper("configurator")->__("Add Font");
		}
	}

	
}