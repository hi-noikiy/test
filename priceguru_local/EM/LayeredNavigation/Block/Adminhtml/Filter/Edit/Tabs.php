<?php
class EM_LayeredNavigation_Block_Adminhtml_Filter_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	protected $_generalTabBlock = 'layerednavigation/adminhtml_filter_edit_tab_general';
	
	protected $_optionsTabBlock = 'layerednavigation/adminhtml_filter_edit_tab_options';

	public function __construct() {
		parent::__construct();
		$this->setId('filter_tabs');
		$this->setDestElementId('edit_form');
		$this->setTitle(Mage::helper('layerednavigation')->__('Filter Information'));
	}

	protected function _beforeToHtml() {
		$this->addTab('general', array(
			'label' => Mage::helper('layerednavigation')->__('General'),
			'title' => Mage::helper('layerednavigation')->__('General'),
			'content' => $this->getLayout()->createBlock($this->_generalTabBlock)->toHtml()
		));

		$this->addTab('images', array(
			'label' => Mage::helper('layerednavigation')->__('Options And Images'),
			'title' => Mage::helper('layerednavigation')->__('Options And Images'),
			'content' => $this->getLayout()->createBlock($this->_optionsTabBlock)->toHtml()
		));
		return parent::_beforeToHtml();
	}

}
