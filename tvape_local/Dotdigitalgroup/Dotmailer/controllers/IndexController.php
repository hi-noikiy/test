<?php 
class Dotdigitalgroup_Dotmailer_IndexController extends Mage_Core_Controller_Front_Action {        

    public function getlastorderAction() {
		$config = Mage::getStoreConfig('dotmailer');
		$return = array('success'=> false);
		if($type = $this->getRequest()->getParam('type'))
		if($type == 'track')
		if($config['dotMailer_group']['dotMailer_track_conversions'])
		{
			if($order_id = Mage::getSingleton('checkout/session')->getLastOrderId())
			{
				$order = Mage::getModel('sales/order')->load($order_id);
				$checkout_amount = 0;
				$products = array();
				foreach($order->getAllItems() as $item)
				{
					$products[] = $item->getName();
					$checkout_amount += $item->getPrice() * $item->getData('qty_ordered');
				}
				$return = array('success'=> true, 'products' => $products, 'checkoutamount' =>$checkout_amount);
			}
		}
		
		echo Zend_Json::encode($return);
    }

} 