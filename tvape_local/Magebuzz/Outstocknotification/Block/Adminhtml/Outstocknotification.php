<?php
/*
 * @copyright (c) 2015 www.magebuzz.com
 */
class Magebuzz_Outstocknotification_Block_Adminhtml_Outstocknotification extends Mage_Adminhtml_Block_Widget_Grid_Container {
  public function __construct() {
    $this->_controller = 'adminhtml_outstocknotification';
    $this->_blockGroup = 'outstocknotification';
    $this->_headerText = Mage::helper('outstocknotification')->__('Out of Stock Subscription');
    parent::__construct();
		$this->_removeButton('add');
  }
}