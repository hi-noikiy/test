<?php
class EM_Customercomment2_Model_Observer
{

			public function saveQuoteBefore(Varien_Event_Observer $observer)
			{
				$quote = $observer->getQuote();
				$post = Mage::app()->getFrontController()->getRequest()->getPost();
				if(isset($post['customercomment2']['ssn'])){
					$var = $post['customercomment2']['ssn'];
					$quote->setSsn($var);
				}
			}
		
			public function saveQuoteAfter(Varien_Event_Observer $observer)
			{
				$quote = $observer->getQuote();
				if($quote->getSsn()){
					$var = $quote->getSsn();
					if(!empty($var)){
						$model = Mage::getModel('customercomment2/customercomment2_quote');
						$model->deteleByQuote($quote->getId(),'ssn');
						$model->setQuoteId($quote->getId());
						$model->setKey('ssn');
						$model->setValue($var);
						$model->save();
					}
				}
			}
		
			public function loadQuoteAfter(Varien_Event_Observer $observer)
			{
				$quote = $observer->getQuote();
				$model = Mage::getModel('customercomment2/customercomment2_quote');
				$data = $model->getByQuote($quote->getId());
				foreach($data as $key => $value){
					$quote->setData($key,$value);
				}
			}
		
			public function saveOrderAfter(Varien_Event_Observer $observer)
			{
				$order = $observer->getOrder();
				$quote = $observer->getQuote();
				if($quote->getSsn()){
					$var = $quote->getSsn();
					if(!empty($var)){
						$model = Mage::getModel('customercomment2/customercomment2_order');
						$model->deleteByOrder($order->getId(),'ssn');
						$model->setOrderId($order->getId());
						$model->setKey('ssn');
						$model->setValue($var);
						$order->setSsn($var);
						$model->save();
					}
				}
			}
		
			public function loadOrderAfter(Varien_Event_Observer $observer)
			{
				$order = $observer->getOrder();
				$model = Mage::getModel('customercomment2/customercomment2_order');
				$data = $model->getByOrder($order->getId());
				foreach($data as $key => $value){
					$order->setData($key,$value);
				}
			}
		
}
