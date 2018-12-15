<?php


class Ktpl_Salesreport_Block_Adminhtml_Salesdata_Renderer_Revenue extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {   
       return $row->getData('order_quantity')*$row->getData('price') ? $row->getData('order_quantity')*$row->getData('price') : '0';
    }

}