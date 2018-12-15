<?php
class  HN_Salesforce_Block_Adminhtml_Map_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form();
		$this->setForm($form);

		$fieldset = $form->addFieldset('general_form', array('legend'=>Mage::helper('salesforce')->__('Rule')));	
		
		$mapping = Mage::registry('mapping');
		$model = Mage::getModel('salesforce/field');
		$isDisabled = false;
		$magento_value = '';
		$salesforce_value = '';
		$name = '';
		$type = '';
		$mageFields = [];
		$salesFields = [];
		
		/* Pass data to form */
		if($mapping){	
			$type = $mapping->getType();
			$magento_value = $mapping->getMagento();
			$salesforce_value = $mapping->getSalesforce();
			$isDisabled = true;
			$name = $mapping->getName();
			$table = $model->getAlltable();		
			$salesFields = $model->getSalesforceFields($type);
			$mageFields = $model->getMagentoFields($table[$type]);
		}

		$fields = $model->changeFields();
		$fieldset->addField('type', 'select', 
			[
				'label' => Mage::helper('salesforce')->__('Select Table'),
				'class' => 'required-entry',
				'required' => true,
				'options' => $fields,
				'name' => 'type',
				'disabled' => $isDisabled,
				'value' => $type,
				'after_element_html' => '<button type="button" id="updateFields">Update Fields</button>' 
			]
		);
	
		$fieldset->addField('magento', 'select', 
			[
				'label' => Mage::helper('salesforce')->__('Magento field'),
				'class' => 'required-entry',
				'required' => true,
				'options' => $mageFields,
				'name' => 'magento',
				'value' => $magento_value
			]
		);

		$fieldset->addField('salesforce', 'select', 
			[
				'label' => Mage::helper('salesforce')->__('Salesforce field'),
				'class' => 'required-entry',
				'required' => true,
				'options' => $salesFields,
				'name' => 'salesforce',
				'value' => $salesforce_value
			]
		);
		
		$fieldset->addField('status', 'select', 
			[
				'label' => Mage::helper('salesforce')->__('Status'),
				'class' => 'required-entry',
				'required' => true,
				'options' =>[
					'1' => __('Active'),
					'0' => __('In active') 
				],
				'name' => 'status',
			]
		);
			
		$fieldset->addField('name', 'textarea', array(
				'label' => Mage::helper('salesforce')->__('Description'),
				'class' => 'required-entry',
				'required' => true,
				'name' => 'name',
				'value' => $name
		));		
	}
}