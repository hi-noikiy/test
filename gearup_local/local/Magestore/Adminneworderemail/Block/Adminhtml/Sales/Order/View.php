<?php
/**
 * Sales order view
 */

class Magestore_Adminneworderemail_Block_Adminhtml_Sales_Order_View extends Magestore_Adminneworderemail_Block_Adminhtml_Sales_Order_View_Amasty_Pure
{
    public function __construct()
    {
        parent::__construct();

        $order = $this->getOrder();

        if ($this->_isAllowedAction('cancel') && $order->canCancel()) {
            $message = Mage::helper('sales')->__("Are you sure you want to cancel this order?\\nThis operation will NOT send any message to observers!");
            $this->_addButton('order_silent_cancel', array(
                'label'     => Mage::helper('sales')->__('Silent Cancel'),
                'onclick'   => 'deleteConfirm(\''.$message.'\', \'' . $this->getSilentCancelUrl() . '\')',
            ));
        }
    }

    public function getSilentCancelUrl()
    {
        return $this->getUrl('*/*/cancel', array('_query' => array('silent' => 1)));
    }
}