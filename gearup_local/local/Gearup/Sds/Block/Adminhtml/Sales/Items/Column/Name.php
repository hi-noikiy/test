<?php

class Gearup_Sds_Block_Adminhtml_Sales_Items_Column_Name extends Mage_Adminhtml_Block_Sales_Items_Column_Name
{
    public function getPartnumber()
    {
        $product = Mage::getModel('catalog/product')->load($this->getItem()->getProductId());
        return $product->getData('part_nr');
    }

    public function getSdsmark()
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $product = Mage::getModel('catalog/product')->load($this->getItem()->getProductId());
        $period = Mage::getModel('hordermanager/order')->getCollection();
        $period->addFieldToFilter('order_id', $orderId);
        $periodF = $period->getFirstItem();
        if ($period->getSize()) {
            if (Mage::helper('gearup_sds')->getSdsHorder($product,$periodF->getPeriodId(),$orderId)) {
                return 'green';
            }
        }
        return '';
    }

    public function saveSds()
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $product = Mage::getModel('catalog/product')->load($this->getItem()->getProductId());
        $period = Mage::getModel('hordermanager/order')->getCollection();
        $period->addFieldToFilter('order_id', $orderId);
        $periodF = $period->getFirstItem();
        if ($period->getSize()) {
            Mage::helper('gearup_sds')->getSdsHorder($product,$periodF->getPeriodId(),$orderId);
        }
    }
}
?>
