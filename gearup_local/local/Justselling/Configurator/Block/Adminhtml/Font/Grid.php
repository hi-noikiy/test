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
 
class Justselling_Configurator_Block_Adminhtml_Font_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		Mage::Log("Cool!");
		parent::__construct();
		$this->setId("configuratorFontGrid");	
		
		$this->setDefaultSort("id");
		$this->setDefaultDir("ASC");
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel("configurator/font")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn("id",array(
			"header"	=>	Mage::helper("configurator")->__("ID"),
			"align"		=>	"right",
			"width"		=>	"50px",
			"index"		=>	"id"
		));
		
		$this->addColumn("title",array(
			"header"	=>	Mage::helper("configurator")->__("Title"),
			"align"		=>	"left",
			"index"		=>	"title"
		));
		
		$this->addColumn("font_type",array(
			"header"	=>	Mage::helper("configurator")->__("Font Type"),
			"align"		=>	"left",
			"index"		=>	"font_type"
		));
				
		$this->addColumn("order",array(
			"header"	=>	Mage::helper("configurator")->__("Sort Order"),
			"align"		=>	"left",
			"index"		=>	"order"
		));
		


	}	
	
	
	
	public function getRowUrl($row)
	{
		return $this->getUrl("*/*/edit",array("id" => $row->getId()));
	}
}