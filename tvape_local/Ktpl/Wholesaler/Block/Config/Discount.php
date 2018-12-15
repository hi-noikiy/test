<?php

class Ktpl_Wholesaler_Block_Config_Discount extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function _prepareToRender()
    {
        $this->addColumn('name', array(
            'label' => Mage::helper('wholesaler')->__('Name'),
            'style' => 'width:100px',
        ));
        $this->addColumn('total', array(
            'label' => Mage::helper('wholesaler')->__('Order Total'),
            'style' => 'width:100px',
        ));
        $this->addColumn('discount', array(
            'label' => Mage::helper('wholesaler')->__('Discount Percent'),
            'style' => 'width:100px',
        ));
 
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('wholesaler')->__('Add');
    }
}
