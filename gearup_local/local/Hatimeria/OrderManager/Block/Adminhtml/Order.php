<?php
/**
 * Period Container
 */
class Hatimeria_OrderManager_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    protected $_blockGroup = 'hordermanager';

    public function __construct()
    {
        $this->_controller = 'adminhtml_order';
        $this->_headerText = Mage::helper('hordermanager')->__('Orders in Periods');

        parent::__construct();
    }
}