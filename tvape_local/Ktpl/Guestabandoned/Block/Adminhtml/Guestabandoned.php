<?php

class Ktpl_Guestabandoned_Block_Adminhtml_Guestabandoned extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_guestabandoned';
	    $this->_blockGroup = 'guestabandoned';
	    $this->_headerText = Mage::helper('guestabandoned')->__('Guest Abandoned Cart');
	 
	    parent::__construct();
		$this->_removeButton('add');
	}
        
        protected function _prepareLayout()
        {
            $this->_addButton('add_new', array(
                'label'   => Mage::helper('catalog')->__('Refresh'),
                'onclick' => "setLocation('{$this->getUrl('*/*/refresh')}')",
                'class'   => 'scalable'
            ));

            return parent::_prepareLayout();
        }
}