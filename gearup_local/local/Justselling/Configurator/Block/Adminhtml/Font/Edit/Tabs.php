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
 * @copyright   Copyright (c) 2011 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 */
 
class Justselling_Configurator_Block_Adminhtml_Font_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("font_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle( Mage::helper("configurator")->__("Font Information") );
	}
	
	protected function _beforeToHtml()
	{	
		$this->addTab("form_font", array(
			"label" => Mage::helper("configurator")->__("Font"),
			"title" => Mage::helper("configurator")->__("Font"),
			"content" => $this->getLayout()->createBlock("configurator/adminhtml_font_edit_tab_font")->toHtml()
		));
		
		return parent::_beforeToHtml();
	}
}
