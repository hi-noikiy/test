<?php

class Hatimeria_OrderManager_Block_Adminhtml_Order_View extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    protected $_objectId = 'period_has_order_id';
    protected $_blockGroup = 'hordermanager';
    protected $_controller = 'adminhtml_order';

    protected function _construct()
    {
        parent::_construct();
    }

    protected function _prepareLayout()
    {

    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return Mage::helper('hordermanager')->__("Add New Order");
    }

} 