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
 
class Justselling_Configurator_Block_Adminhtml_Font extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = "adminhtml_font";
		$this->_blockGroup = "configurator";
		$this->_headerText = Mage::helper("configurator")->__("Font Manager");
		$this->_addButtonLabel = Mage::helper("configurator")->__("Add Font");	
		
		parent::__construct();		
	}
}