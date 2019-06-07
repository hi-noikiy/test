<?php
class Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Destination
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
        $orderId = $row->getOrderId();
        $order = Mage::getModel('sales/order')->load($orderId);
        $country = Mage::getModel('directory/country')->loadByCode($order->getShippingAddress()->getCountryId());
        return $country->getName();
    }

}
