<?php
/*
* @Copyright (c) 2015 www.magebuzz.com
*/ 
class Magebuzz_Outstocknotification_IndexController extends Mage_Core_Controller_Front_Action {
  public function indexAction() {	
		$this->loadLayout();  
		$this->_initLayoutMessages('catalog/session');
		$this->renderLayout();
  }
	
	public function viewAction() {
		$this->loadLayout();     
		$this->renderLayout();
	}

	public function popupnotifyAction() {        
		$this->loadLayout();     
		$this->renderLayout();
	}
  
  public function popuplistAction() {        
    $this->loadLayout();     
    $this->renderLayout();
  }

	public function stoctnotifyAction() {
    $successMessage = Mage::getStoreConfig('outstocknotification/general/success_message');
    $errorMessage = Mage::getStoreConfig('outstocknotification/general/error_message');
		$isArray = $this->getRequest()->getParams(); 
		$session = Mage::getModel('customer/session');
		if ($session->isLoggedIn()) {
			$customer = $session->getCustomer()->getData(); 
			$isArray['customer_id']= $customer['entity_id'];
		} else {
			$isArray['customer_id']= 0;
		}  
		$result = Mage::getModel('outstocknotification/outstocknotification')->addDataNotify($isArray);
		if (is_array($result) && $result['success']) {
			echo '<div style="margin-top:30px;" id="messages_shoppinglist">
							<ul class="messages">
								<li class="success-msg">
									<ul>
										<li>
											<span>'.$successMessage.'</span>
										</li>
									</ul>
								</li>
							</ul>
						</div>';
		} else {
			echo '<div style="margin-top:30px;" id="messages_shoppinglist">
							<ul class="messages">
								<li class="error-msg">
									<ul>
										<li>
											<span>' . isset($result['message']) ? $result['message'] : $errorMessage. '</span>
										</li>
									</ul>
								</li>
							</ul>
						</div>';
		}  
		$this->loadLayout();
		$this->renderLayout();
	}
	
  public function viewstockAction() {
		$this->loadLayout();
		$this->_initLayoutMessages('core/session');
		$this->getLayout()->getBlock('head')->setTitle(Mage::helper('outstocknotification')->__('My Out of Stock Subscriptions'));        
		if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
			$block->setRefererUrl($this->_getRefererUrl());
		}
		$this->renderLayout();
   }

  public function deletenotifiAction() {
		$session = Mage::getSingleton('customer/session');
		if ($session->isLoggedIn()) {
			$customer = $session->getCustomer()->getData(); 
			$notifi_id = $this->getRequest()->getParam('notifi_id');
			$deletenotifi = Mage::getModel('productalert/stock');
			$deletenotifi->load($notifi_id);
			$deletenotifi->delete();
			$this->_redirect('*/*/viewstock/');   
		}   
	}
}