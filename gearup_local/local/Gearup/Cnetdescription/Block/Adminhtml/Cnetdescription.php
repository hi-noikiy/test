<?php

class Gearup_Cnetdescription_Block_Adminhtml_Cnetdescription extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_cnetdescription';
        $this->_blockGroup = 'cnetdescription';
        $this->_headerText = Mage::helper('cnetdescription')->__('Cnet Description');

        parent::__construct();
        $this->_removeButton('add');
    }
}
