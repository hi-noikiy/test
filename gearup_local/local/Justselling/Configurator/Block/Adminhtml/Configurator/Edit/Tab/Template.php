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
 * @copyright   Copyright (C) 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
**/
 
class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Template extends Mage_Adminhtml_Block_Widget_Form
{
	
	protected function getLinkdedProductsSelectHtml()
	{
		$list = array();
	
		$options = Mage::getModel("configurator/template")->getLinkedProducts(Mage::registry("configurator_data")->getId());
		foreach ($options as $option_id) {
			$option = Mage::getModel("catalog/product_option")->load($option_id);
			$product = Mage::getModel("catalog/product")->load($option->getProductId());
			$list[$product->getId()] = $product->getname()." [".$product->getId()."]";
		}
	
		// Mage::Log("list=".var_export($list,true));
		return $list;
	}
	
	protected function getLinkdedProductOptionsSelectHtml()
	{
		$list = array();
		
		$options = Mage::getModel("configurator/template")->getLinkedProducts(Mage::registry("configurator_data")->getId());
		foreach ($options as $option_id) {
			$option = Mage::getModel("catalog/product_option")->load($option_id);
			$product = Mage::getModel("catalog/product")->load($option->getProductId());
			$title = "";
			foreach ($product->getOptions() as $_option) {
				if ($_option->getId() == $option->getId()) {
					$title = $_option->getTitle();
					break;
				}
			}
			
			$list[$option->getId()]= $title." [".$option->getId()."]";
		}
		
		return $list;
	}	
	
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		/* Template Information */
		$fieldset = $form->addFieldset("configurator_form", array(
			"legend" => Mage::helper('configurator')->__('Template Information')
		));
		
		$fieldset->addField("title","text",array(
			"label" => Mage::helper('configurator')->__('Title'),
			"class" => "required-entry",
			"required" => true,
			"name" => "title"
		));
		
		$fieldset->addField("headline","text",array(
			"label" => Mage::helper('configurator')->__('Headline'),
			"name" => "headline"
		));

		/**
		$fieldset->addField("template_image","image",array(
			"label"     => Mage::helper('configurator')->__('Headline Image'),
          	"required"  => false,
          	"name"      => "template_image",
		));
		 **/

		$fieldset->addType('templateimage', 'Justselling_Configurator_Block_Adminhtml_Form_Renderer_Fieldset_Templateimage');
		$fieldset->addField('template_image', 'templateimage', array(
			"label"     => Mage::helper('configurator')->__('Headline Image'),
			"name"      => "template_image",
			"type"      => "templateimage",
		));
		
		/* ERP Integration */
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("P","U"))) {
			$fieldset = $form->addFieldset("erp_integration", array(
				"legend" => Mage::helper('configurator')->__('ERP Integration')
			));	
			
			$fieldset->addField("alt_checkout","select",array(
				"label" => Mage::helper('configurator')->__('Linked Product Checkout'),
				"name" => "alt_checkout",
				"options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
			));
		}

		/* Vector Export */
		if (in_array(Mage::getSingleton('core/session')->getEdition(),array("U"))) {
			$fieldset = $form->addFieldset("vector_export", array(
					"legend" => Mage::helper('configurator')->__('Vector Graphics Export')
			));
		
			$fieldset->addField("svg_export","select",array(
				"label" => Mage::helper('configurator')->__('SVG Export'),
				"name" => "svg_export",
				"options" => array(1=>Mage::helper('configurator')->__('Yes'),0=>Mage::helper('configurator')->__('No'))
			));
			
			$fieldset->addField("mass_factor","text",array(
				"label" => Mage::helper('configurator')->__('Factor to calculate inch from width and height values'),
				"name" => "mass_factor"	));
		}		
		
		/* Products */
		$fieldset = $form->addFieldset("products", array(
				"legend" => Mage::helper('configurator')->__('Products')
		));
		
		$fieldset->addField("linked_products","select",array(
				"label" => Mage::helper('configurator')->__('Linked Products'),
				"name" => "linked_products",
				"options" => $this->getLinkdedProductsSelectHtml()
		));
		
		$fieldset->addField("linked_product_options","select",array(
				"label" => Mage::helper('configurator')->__('Linked Product Options'),
				"name" => "linked_product_options",
				"options" => $this->getLinkdedProductOptionsSelectHtml()
		));		
		
		if( Mage::getSingleton("adminhtml/session")->getConfiguratorData() )
		{
			$form->setValues( Mage::getSingleton("adminhtml/session")->getConfiguratorData() );
			Mage::getSingleton("adminhtml/session")->setConfiguratorData(null);
		}
		elseif ( Mage::registry("configurator_data") )
		{
			$form->setValues( Mage::registry("configurator_data")->getData() );
		}
		
		// Mage::Log("values=".var_export($form->getValues(),true));
		
		return parent::_prepareForm();
	}

    protected function _afterToHtml($html)
    {
        $block = Mage::helper('configurator')->getHelpIqLink(
            $this,
            "helpiq-lightbox",
            Mage::helper('configurator')->__('settings-of-the-template'),
            Mage::helper('configurator')->__('Settings of the template')
        );
        $block .= " ".$this->__("Module Version").": ".Mage::getConfig()->getNode()->modules->Justselling_Configurator->version."-".Mage::getSingleton('core/session')->getEdition();
        return  $block.$html;
    }
}