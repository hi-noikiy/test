<?php

class Amasty_Email_Block_Adminhtml_Sales_Order_View_Tabs extends Mage_Adminhtml_Block_Sales_Order_View_Tabs
{
    protected function _beforeToHtml()
    {
        $id = Mage::registry('current_order')->getId();
        if ($id) {
            $this->addTab('amemail', array(
                'label'     => Mage::helper('amemail')->__('Emails History'),
                'class'     => 'ajax',
                'url'       => $this->getUrl('amemail/adminhtml_index/order', array('order_id' => $id)),
                //'after'     => 'tags',
            ));
        }
        return parent::_beforeToHtml();
    }
}