<?php

class Gearup_Countdown_Block_Adminhtml_Dailydeal extends  Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'gearup_countdown';
        $this->_controller = 'adminhtml_dailydeal';
        $this->_headerText = $this->__('Daily Deal');
         
        parent::__construct();
        $this->_removeButton('add');
    }
    
}