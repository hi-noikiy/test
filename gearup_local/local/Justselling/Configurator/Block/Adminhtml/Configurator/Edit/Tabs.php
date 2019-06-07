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
 
class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("configurator_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle( Mage::helper("configurator")->__("Template Information") );
	}
	
	protected function _beforeToHtml()
	{
		$this->addTab("form_section", array(
			"label" => Mage::helper("configurator")->__("Template"),
			"title" => Mage::helper("configurator")->__("Template"),
			"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_template")->toHtml()
		));
		
		$this->addTab("form_design", array(
				"label" => Mage::helper("configurator")->__("Design"),
				"title" => Mage::helper("configurator")->__("Design"),
				"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_design")->toHtml()
		));
		
		$this->addTab("form_groups", array(
			"label" => Mage::helper("configurator")->__("Option Groups"),
			"title" => Mage::helper("configurator")->__("Option Groups"),
			"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_groups")->toHtml()
		));
		
		$this->addTab("form_options", array(
			"label" => Mage::helper("configurator")->__("Options"),
			"title" => Mage::helper("configurator")->__("Options"),
			"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_options")->toHtml()
		));
		$this->addTab("form_suboptions", array(
			"label" => Mage::helper("configurator")->__("Sub Sections"),
			"title" => Mage::helper("configurator")->__("Sub Sections"),
			"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_suboptions")->toHtml()
		));
		
		$this->addTab("form_postpricerules", array(
			"label" => Mage::helper("configurator")->__("Post Price Rules"),
			"title" => Mage::helper("configurator")->__("Post Price Rules"),
			"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_postpricerules")->toHtml()
		));

        if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) {
            $this->addTab("form_rules", array(
                "label" => Mage::helper("configurator")->__("Rules"),
                "title" => Mage::helper("configurator")->__("Rules"),
                "content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_rules")->toHtml()
            ));
        }

	/*	if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) {
			$this->addTab("form_singleproducts", array(
				"label" => Mage::helper("configurator")->__("Single Products"),
				"title" => Mage::helper("configurator")->__("Single Products"),
				"content" => $this->getLayout()->createBlock("configurator/adminhtml_configurator_edit_tab_singleproducts")->toHtml()
			));
		} */
		
		return parent::_beforeToHtml();
	}
}