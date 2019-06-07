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
 
class Justselling_Configurator_Block_Adminhtml_Font_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form(array(
			"id" => "edit_form",
			"action" => $this->getUrl("*/*/save", array(
				"id" => $this->getRequest()->getParam("id")				
			)),
			"method" => "post",
			"enctype" => "multipart/form-data"
		));
		
		$form->setUseContainer(true);
		$this->setForm($form);
		
		return parent::_prepareForm();
	}
}