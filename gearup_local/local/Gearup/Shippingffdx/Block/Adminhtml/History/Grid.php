<?php

class Gearup_Shippingffdx_Block_Adminhtml_History_Grid extends FFDX_ShippingBox_Block_Adminhtml_History_Grid
{
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

//        $this->addColumn('location', array(
//            'header'    => Mage::helper('ffdxshippingbox')->__('Location'),
//            'align'     => 'left',
//            'index'     => 'location',
//            'renderer'  => 'Gearup_Shippingffdx_Block_Adminhtml_History_Renderer_Location',
//        ));

        return $this;
    }
}