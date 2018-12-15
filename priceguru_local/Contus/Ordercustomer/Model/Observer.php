<?php
/**
 * Contus Support Interactive.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file GCLONE-LICENSE.txt.
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento 1.4.1.1 COMMUNITY edition
 * Contus Support does not guarantee correct work of this package
 * on any other Magento edition except Magento 1.4.1.1 COMMUNITY edition.
 * =================================================================
 */
class Contus_Ordercustomer_Model_Observer extends Varien_Object
{
	public function createInvoiceSaveAfter($observer) {
		$data = array();
		$datapickup = array();
		$order = $observer->getEvent()->getOrder();
		//echo "<pre>"; print_r($order->getData()); print_r($order->getBillingAddress()->getData()); die();
		$data_coll = Mage::app()->getRequest()->getParams();
		//$resource = Mage::getSingleton('core/resource');
		//$writeConnection = $resource->getConnection('core_write');
		$user = Mage::getSingleton('admin/session');
		$username = $user->getUser()->getUsername();

		$data['increment_id'] = $datapickup['order_id'] = $order->getIncrementId();
        //Mage::log('Order Id: '.$data['increment_id'], null, 'pickuporder.log');
		$datapickup['real_order_id'] = $order->getId(); 
		$data['username']     = $username;
		$data['payment_type'] = $data_coll['payment_information'];
		$datapickup['payment_method'] = ($order->getIscimorder()) ? "CIM" : $order->getPayment()->getMethodInstance()->getTitle();
		$data['invoice_comment'] = $data_coll['invoice']['comment_text'];
		$data['order_created_date'] = $order->getCreatedAt();
		$datapickup['order_created_date'] = date('Y-m-d H:i:s');
		$data['customer_name'] = $datapickup['customer_name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
                if(trim($datapickup['customer_name']) ==''){
                    $data['customer_name'] = $datapickup['customer_name'] = $order->getBillingAddress()->getName();
                }    
		$datapickup['telephone'] = $order->getBillingAddress()->getData('telephone');
		$datapickup['address'] = $order->getBillingAddress()->getData('street');
		$datapickup['delivery_comment'] = $order->getCustomerNote();

		$items = $order->getAllItems();
		foreach($items as $item) {
			if($item->getData('product_options')) {
				$opts = unserialize($item->getData('product_options'));
				$custom_option = $opts['options'][0]['value']; 	
			} else {
				$custom_option = "";
			}
			
			$data['product_name'] = $datapickup['product_name'] = $item->getData('name');
			$data['product_subtitle'] = $item->getProduct()->getNewSku();
			$data['customtitle'] = $datapickup['attributes'] = $custom_option;
			$data['product_sku'] = $datapickup['sku'] = $item->getSku();
			$data['price'] = $datapickup['retail_price'] = $item->getPrice();
			$datapickup['qty'] = round($item->getQtyOrdered());
			$datapickup['status'] = 1;

			/*$model = Mage::getModel('ordercustomer/ordercustomer');
        	$model->setData($data);
        	$model->setCreatedTime(now());
        	$model->save(); */

        	/* Insert data for pickup order */
                $pickup = Mage::getModel('customreport/poorder')->getCollection()
                           ->addFieldToFilter('sku',$item->getSku())
                           ->addFieldToFilter('order_id',$order->getIncrementId())
                           ->getFirstItem();

                    if($pickup->getPoId()){
                        $pickupmodel = Mage::getModel('customreport/poorder')->load($pickup->getPoId());
                        
                    }
                    else{
                        $pickupmodel = Mage::getModel('customreport/poorder');
                        $pickupmodel->setData($datapickup);
                        $pickupmodel->save();
                    }
                          	
		}

		//$writeConnection -> insert('ordercustomer_payment', $data);
	}

	public function shipmentCreateAfter(Varien_Event_Observer $observer) {

		$data = array();
		$datapickup = array();
		$shipment = $observer->getEvent()->getShipment();
		$order = $shipment->getOrder(); 
        //$order = $observer->getEvent()->getOrder();
		//echo "<pre>"; print_r($order->getData()); print_r($order->getBillingAddress()->getData()); die();
		//$data_coll = Mage::app()->getRequest()->getParams();
		//$resource = Mage::getSingleton('core/resource');
		//$writeConnection = $resource->getConnection('core_write');
		$user = Mage::getSingleton('admin/session');
		$username = $user->getUser()->getUsername();
		//$payment_info = ($order->getIscimorder()) ? "CIM" : $order->getPayment()->getMethodInstance()->getTitle();
		$payment_info = $_POST['payment_information']; 

		$data['increment_id'] = $datapickup['order_id'] = $order->getIncrementId();
		$datapickup['real_order_id'] = $order->getId(); 
		$data['username']     = $username;
		$data['payment_type'] = $payment_info;
		$datapickup['payment_method'] = $payment_info;
		$data['invoice_comment'] = "";
		$data['order_created_date'] = $order->getCreatedAt();
		$datapickup['order_created_date'] = date('Y-m-d H:i:s');
		$data['customer_name'] = $datapickup['customer_name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();
		$datapickup['telephone'] = $order->getBillingAddress()->getData('telephone');
		$datapickup['address'] = $order->getBillingAddress()->getData('street');
		$datapickup['delivery_comment'] = $order->getCustomerNote();

		$items = $order->getAllItems();
		foreach($items as $item) {
			if($item->getData('product_options')) {
				$opts = unserialize($item->getData('product_options'));
				$custom_option = $opts['options'][0]['value']; 	
			} else {
				$custom_option = "";
			}
			
			$data['product_name'] = $datapickup['product_name'] = $item->getData('name');
			$data['product_subtitle'] = $item->getProduct()->getNewSku();
			$data['customtitle'] = $datapickup['attributes'] = $custom_option;
			$data['product_sku'] = $datapickup['sku'] = $item->getSku();
			$data['price'] = $datapickup['retail_price'] = $item->getPrice();
			$datapickup['qty'] = round($item->getQtyOrdered());
			$datapickup['status'] = 1;

			$model = Mage::getModel('ordercustomer/ordercustomer');
        	$model->setData($data);
        	$model->setCreatedTime(date('Y-m-d H:i:s'));
        	$model->save();

        	/* Insert data for pickup order */
        	/*$pickupmodel = Mage::getModel('customreport/salespickuporder');
        	$pickupmodel->setData($datapickup);
        	$pickupmodel->save();*/
		}

	}

	public function saveOrderComment($observer)
    {
        $orderComment = Mage::app()->getRequest()->getPost('customercomment');

        if (isset($orderComment) && $orderComment != "") {
            $comment = trim($orderComment);
            $order = $observer->getEvent()->getOrder(); 
            $order->setCustomerComment($comment);
            $order->setCustomerNoteNotify(true);
            $order->setCustomerNote($comment);
        }
    }
    
    public function Addusername($observer){
        $invoice = $observer->getEvent()->getInvoice();
        $user = Mage::getSingleton('admin/session');
	$username = $user->getUser()->getUsername();
                
        $resource = Mage::getSingleton('core/resource');
        $read = $resource->getConnection('core_read');
        $write = $resource->getConnection('core_write');
        $query = "UPDATE sales_flat_invoice_grid SET username = '{$username}' WHERE entity_id = "
			 . $invoice->getEntityId();
        $write->query($query);
    }
    
}