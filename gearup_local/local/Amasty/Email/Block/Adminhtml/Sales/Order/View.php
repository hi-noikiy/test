<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Email_Block_Adminhtml_Sales_Order_View extends Magestore_Adminneworderemail_Block_Adminhtml_Sales_Order_View
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        if ($this->getOrderId()){
            $this->_addButton('send_email', array(
                'label'     => Mage::helper('amemail')->__('Send Email...'),
                'onclick'   => "setLocation('{$this->_getSendEmailUrl()}')",
            ), 0);
        }

        return parent::_prepareLayout();
    }

    protected function _getSendEmailUrl()
    {
        return $this->getUrl('amemail/adminhtml_index/index', array(
            'order_id' => $this->getOrderId(),
        ));
    }
}
