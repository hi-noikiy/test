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
 
class Justselling_Configurator_Block_Adminhtml_Configurator_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("configuratorGrid");	
		
		$this->setDefaultSort("id");
		$this->setDefaultDir("ASC");
		$this->setSaveParametersInSession(true);
	}
	
	protected function _prepareCollection()
	{
		$collection = Mage::getModel("configurator/template")->getCollection();
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

	}	
	
	
	
	public function getRowUrl($row)
	{
		return $this->getUrl("*/*/edit",array("id" => $row->getId()));
	}
}