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
 
class Justselling_Configurator_Block_Adminhtml_Font_Edit_Tab_Font extends Mage_Adminhtml_Block_Widget_Form
{
	
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("font_form", array(
			"legend" => Mage::helper('configurator')->__('Font Information')
		));
		
		$fieldset->addField("title","text",array(
			"label" => Mage::helper('configurator')->__('Title'),
			"class" => "required-entry",
			"required" => true,
			"name" => "title"
		));

		$fieldset->addField('font_file', 'file', array(
          	'label'     => Mage::helper('configurator')->__('Font File'),
          	'required'  => false,
          	'name'      => 'font_file',
		));
		 
		$fieldset->addField('font_type', 'select', array(
        	'name'  => 'font_type',
        	'label'     => Mage::helper('configurator')->__('Font Type'),
        	'values'    => array(0=>'Regular',1=>'Italic',2=>'Bold',3=>'BoldIatlic'),
   		));
		
        $fieldset->addField("order","text",array(
			"label" => Mage::helper('configurator')->__('Sort Order'),
			"name" => "order"
		));
		
		if( Mage::getSingleton("adminhtml/session")->getConfiguratorData() ) {
			$form->setValues( Mage::getSingleton("adminhtml/session")->getConfiguratorData() );
			Mage::getSingleton("adminhtml/session")->setConfiguratorData(null);
		} elseif ( Mage::registry("font_data") ) {
			$form->setValues( Mage::registry("font_data")->getData() );
		}
		
		return parent::_prepareForm();
    }
    

	public function getActivateUrl()
    {
        return $this->getUrl('*/*/activate/id/'.Mage::registry("font_data")->getId()); 
    }
}