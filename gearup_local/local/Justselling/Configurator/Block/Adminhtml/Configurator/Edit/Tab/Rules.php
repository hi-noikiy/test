<?php

/**
 * justselling Germany Ltd. EULA
 * http://www.justselling.de/
 * Read the license at http://www.justselling.de/lizenz
 * Do not edit or add to this file, please refer to http://www.justselling.de for more information.
 * @category    justselling
 * @package     justselling_configurator
 * @copyright   Copyright � 2012 justselling Germany Ltd. (http://www.justselling.de)
 * @license     http://www.justselling.de/lizenz
 **/

class Justselling_Configurator_Block_Adminhtml_Configurator_Edit_Tab_Rules extends Mage_Adminhtml_Block_Widget {
	protected $_data = null;

	public function __construct(){
		parent::__construct();
		$this->setTemplate('configurator/template/rules.phtml');

		$this->_data = Mage::registry("configurator_data")->getData();
	}

	protected function _prepareLayout(){
		$this->setChild('add_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array('label' => Mage::helper('catalog')->__('Add New Rule'), 'class' => 'add', 'id' => 'add_new_rule')));

		$this->setChild('delete_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array('label' => Mage::helper('catalog')->__('Delete Rule'), 'class' => 'delet delete_rule')));
		return parent::_prepareLayout();
	}

	public function getDeleteButtonHtml(){
		return $this->getChildHtml('delete_button');
	}

	public function getAddButtonHtml(){
		return $this->getChildHtml('add_button');
	}

	public function getTemplateData(){
		return $this->_data;
	}

	public function getTemplateId(){
		if(isset($this->_data['id']))
			return $this->_data['id'];
		return null;
	}

	public function getFieldName()
	{
		return "template[rules]";
	}


	public function getFieldId(){
		return "template_rulesrule";
	}

	public function getRulesValues(){
		if(is_null($this->getTemplateId())){
			return null;
		}

		$collection = Mage::getModel('configurator/rules')->getCollection();
		$collection->addFieldToFilter('template_id', array( 'eq' => $this->getTemplateId()));

		$items = array();
		foreach($collection as $item){
			$items[] = $item->getData();
 		}
		return $items;
	}

	public function getLastRulesId(){
		$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
		$rulesTable = Mage::getSingleton("core/resource")->getTableName('configurator/rules');

		$select = $connection->select()->from(array("co" => $rulesTable), array("MAX(id)"));

		$id = $connection->fetchOne($select);

		if($id)
			return $id;

		return 0;
	}


	protected function _afterToHtml($html){
		$block = Mage::helper('configurator')->getHelpIqLink($this, "helpiq-lightbox", Mage::helper('configurator')->__('rules'), Mage::helper('configurator')->__('Rules'));
		return $block . $html;
	}

	public function getRuleOperatorSelectHtml()
	{
		$select = $this->getLayout()->createBlock('adminhtml/html_select')
			->setData(array(
				'id' => $this->getFieldId().'_${id}_operatorvalue',
				'class' => 'select select-template-rules-operatorvalue'
			))
			->setName($this->getFieldName().'[${id}][operatorvalue]')
			->setOptions(array(
				'>' => '>',
				'<' => '<',
				'==' => '==',
				'>=' => '>=',
				'<=' => '<=',
				'!=' => '!='
			));

		return $select->getHtml();
	}

	public function getAppliedforSelectHtml()
	{
		$select = $this->getLayout()->createBlock('adminhtml/html_select')
			->setData(array(
				'id' => $this->getFieldId().'_${id}_appliedfor',
				'class' => 'select select-template-rules-appliedfor'
			))
			->setName($this->getFieldName().'[${id}][appliedfor]')
			->setOptions(array(
				'amount' => Mage::helper('catalog')->__('Amount of values'),
				'count' => Mage::helper('catalog')->__('Option value count'),
			));

		return $select->getHtml();
	}

	public function getScopeSelectHtml()
	{
		$options = array();
		$options['all'] = Mage::helper('catalog')->__('All Elements');
		$options['option'] = Mage::helper('catalog')->__('Option');

		$optionGroupCollection  = Mage::getModel("configurator/optiongroup")->getCollection();
		$optionGroupCollection->addFieldToFilter('template_id', array( 'eq' => $this->getTemplateId()));

		foreach($optionGroupCollection as $optionGroup){
			$options[$optionGroup->getId()] = Mage::helper('catalog')->__('Group: ') .$optionGroup->getTitle();
		}

		$select = $this->getLayout()->createBlock('adminhtml/html_select')
			->setData(array(
				'id' => $this->getFieldId().'_${id}_scope',
				'class' => 'select select-template-rules-scope'
			))
			->setName($this->getFieldName().'[${id}][scope]')
			->setOptions($options);

		return $select->getHtml();
	}

	public function getWhenExecutedSelectHtml()
	{
		$select = $this->getLayout()->createBlock('adminhtml/html_select')
			->setData(array(
				'id' => $this->getFieldId().'_${id}_when_executed',
				'class' => 'select select-template-rules-when-executed'
			))
			->setName($this->getFieldName().'[${id}][when_executed]')
			->setOptions(array(
				'direct-before' => Mage::helper('catalog')->__('Direct before'),
				'cart' => Mage::helper('catalog')->__('Cart'),
				'direct-after' => Mage::helper('catalog')->__('Direct after'),
				'ownbutton' => Mage::helper('catalog')->__('Own Button'),
				'wizard' => Mage::helper('catalog')->__('Wizard'),
			));

		return $select->getHtml();
	}

}