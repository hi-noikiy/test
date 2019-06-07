<?php

class Hatimeria_OrderManager_Block_Adminhtml_Period_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_objectId = 'period_id';
    protected $_blockGroup = 'hordermanager';
    protected $_controller = 'adminhtml_period';

    /**
     * Get edit form container header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        if (Mage::registry('period')->getId()) {
            return Mage::helper('hordermanager')->__("Edit Period '%s'", $this->htmlEscape(Mage::registry('period')->getId()));
        }
        else {
            return Mage::helper('hordermanager')->__('New Order');
        }
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