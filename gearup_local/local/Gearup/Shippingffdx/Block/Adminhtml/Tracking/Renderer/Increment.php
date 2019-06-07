<?php
class Gearup_Shippingffdx_Block_Adminhtml_Tracking_Renderer_Increment
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input
{
    public function render(Varien_Object $row)
    {
    	$incrementId = $row->getIncrementId();
        $orderId = $row->getOrderId();
		$orderView = Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id'=> $orderId));

		return '<a target="_blank" href="' . $orderView . '">' . $incrementId . '</a>';
    }

}
