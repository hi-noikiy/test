<?php 

class Gearup_OrderManager_ShippingController extends Mage_Core_Controller_Front_Action{

	public function estimatePostAction(){

		$product_id	= $this->getRequest()->getParam('product_id',true);
		$country_id	= $this->getRequest()->getParam('country_id',true);
		$_product = Mage::getModel('catalog/product')->load($product_id);
		$rates_ = [];
		if($_product->isSaleable()){
				
				$quote = Mage::getModel('sales/quote');
				$quote->getShippingAddress()->setCountryId($country_id);
				$quote->addProduct($_product); 
				$quote->getShippingAddress()->collectTotals();
				$quote->getShippingAddress()->setCollectShippingRates(true);
				$quote->getShippingAddress()->collectShippingRates();
				$rates = $quote->getShippingAddress()->getShippingRatesCollection();

				foreach ($rates as $rate){
					$rates_[] = ['price'=> $quote->getStore()->convertPrice($rate->getPrice()),'code'=>$rate->getCode(),'title'=> $rate->getMethodTitle()];
				}

				
		}

		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($rates_));
	}

}