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
	public function addPaymentInvoice($observer) {
		$data = array();
		$order = $observer->getEvent()->getOrder();
		$data_coll = Mage::app()->getRequest()->getParams();
		//$resource = Mage::getSingleton('core/resource');
		//$writeConnection = $resource->getConnection('core_write');
		$user = Mage::getSingleton('admin/session');
		$username = $user->getUser()->getUsername();

		$data['increment_id'] = $order->getIncrementId();
		$data['username']     = $username;
		$data['payment_type'] = $data_coll['payment_information'];
		$data['invoice_comment'] = $data_coll['invoice']['comment_text'];
		$data['order_created_date'] = $order->getCreatedAt();
		$data['customer_name'] = $order->getCustomerFirstname().' '.$order->getCustomerLastname();

		$items = $order->getAllItems();
		foreach($items as $item) {
			if($item->getData('product_options')) {
				$opts = unserialize($item->getData('product_options'));
				$custom_option = $opts['options'][0]['value']; 	
			} else {
				$custom_option = "";
			}
			
			$data['product_name'] = $item->getData('name');
			$data['product_subtitle'] = $item->getProduct()->getNewSku();
			$data['customtitle'] = $custom_option;
			$data['product_sku'] = $item->getSku();
			$data['price'] = $item->getPrice();

			$model = Mage::getModel('ordercustomer/ordercustomer');
        	$model->setData($data);
        	$model->setCreatedTime(now());
        	$model->save();
		}
		
		//$writeConnection -> insert('ordercustomer_payment', $data);
	}
}