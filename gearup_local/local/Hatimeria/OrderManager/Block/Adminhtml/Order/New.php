<?php

class Hatimeria_OrderManager_Block_Adminhtml_Order_New extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_objectId = 'order_id';
    protected $_blockGroup = 'hordermanager';
    protected $_controller = 'adminhtml_order';
    protected $_mode = 'new';

    protected function _construct()
    {
        parent::_construct();
    }

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $currentPeriodId = $this->getRequest()->getParam('period_id');
        $currentPeriod = Mage::getModel('hordermanager/period')->load($currentPeriodId);
        $customPeriodId = $currentPeriod->getCustomPeriodId();

        return Mage::helper('hordermanager')->__('Change Orders of Period: %s', $customPeriodId);
    }

    /**
     * get periods edges
     */
    public function getPeriod()
    {
        $periodsCollection = Mage::getModel('hordermanager/period')->getCollection();

        return $periodsCollection;
    }
}