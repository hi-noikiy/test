<?php
class EM_Mobapp_Block_Adminhtml_Mobapp extends Mage_Adminhtml_Block_Widget_Grid_Container
{
  public function __construct()
  {
    $this->_controller = 'adminhtml_mobapp';
    $this->_blockGroup = 'mobapp';
    $this->_headerText = Mage::helper('mobapp')->__('Apps Manager');
    $this->_addButtonLabel = Mage::helper('mobapp')->__('Add App');

	$generalinfo = array();
	if(Mage::registry('mobapp_generalinfo'))
		$generalinfo = Mage::registry('mobapp_generalinfo');

	$check = Mage::helper("mobapp")->checkVersion($generalinfo['latest_version']);
	if($check == 1){
		$this->_addButton('btnUp', array(
			'label' => Mage::helper('mobapp')->__('New Version %s Release',$generalinfo['latest_version']),
			'onclick' => "popWin('". $generalinfo['update_url'] ."','_blank')",
			'class' => 'custom_update_version',
			'target' => '_blank'
		));
	}

    parent::__construct();
  }
}