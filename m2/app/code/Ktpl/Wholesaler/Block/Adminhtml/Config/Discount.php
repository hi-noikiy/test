<?php

namespace Ktpl\Wholesaler\Block\Adminhtml\Config;

class Discount extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    protected function _construct()
    {
        parent::_construct();
       //$this->setTemplate('vendor_namespace::mytemplate.phtml');

    }

    protected function _prepareToRender()
    {
        // Override in descendants to add columns, change add button label etc

        $this->addColumn('name', array(
            'label' => __('Name'),
            'style' => 'width:100px',
        ));
        $this->addColumn('total', array(
            'label' => __('Order Total'),
            'style' => 'width:100px',
            'class' => 'validate-number validate-digits validate-greater-than-zero'
        ));

        $this->addColumn('discount', array(
            'label' => __('Discount Percent'),
            'style' => 'width:100px',
            'class' => 'validate-number validate-greater-than-zero'
        ));
        
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        
    }

}
