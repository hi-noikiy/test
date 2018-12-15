<?php
class HN_Salesforce_Block_Adminhtml_Map_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {
	public function __construct() {
		parent::__construct ();
		$this->setId ( 'lead_map_tabs' );
		$this->setDestElementId ( 'edit_form' );
		$this->setTitle ( Mage::helper ( 'salesforce' )->__ ( 'Field mapping' ) );
	}
	
	protected function _beforeToHtml() {
	$this->addTab ( 'form_section_general', array (
			'label' => Mage::helper ( 'salesforce' )->__ ( 'General' ),
			'title' => Mage::helper ( 'salesforce' )->__ ( 'Field Mapping' ),
			'content' => $this->getLayout ()->createBlock ( 'salesforce/adminhtml_map_edit_tab_form' )->toHtml ()
	) );
	
	return parent::_beforeToHtml ();
}
}