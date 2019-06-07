<?php

class Hatimeria_MassAction_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'sales_order';
        //$this->_headerText = Mage::helper('sales')->__('Orders');
       // $this->_addButtonLabel = Mage::helper('sales')->__('Create New Order');
        parent::__construct();
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create')) {
           // $this->_removeButton('add');
        }
    }

    public function getCreateUrl()
    {
        return $this->getUrl('*/sales_order_create/start');
    }

}