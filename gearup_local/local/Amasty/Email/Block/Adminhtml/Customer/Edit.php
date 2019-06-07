<?php
/**
 * @copyright   Copyright (c) 2010 Amasty (http://www.amasty.com)
 */  
class Amasty_Email_Block_Adminhtml_Customer_Edit extends Mage_Adminhtml_Block_Customer_Edit
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCustomerId()){
            $this->_addButton('send_email', array(
                'label'     => Mage::helper('amemail')->__('Send Email...'),
                'onclick'   => "setLocation('{$this->_getSendEmailUrl()}')",
            ), 12);
        }

        return $this;
    }

    protected function _getSendEmailUrl()
    {
        return $this->getUrl('amemail/adminhtml_index/index', array(
            'customer_id' => $this->getCustomerId(),
        ));
    }
}
