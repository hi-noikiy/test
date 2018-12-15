<?php
/*
* @copyright (c) 2015 www.magebuzz.com
*/ 
class Magebuzz_Outstocknotification_Model_Outstocknotification extends Mage_Core_Model_Abstract {
  public function _construct() {
    parent::_construct();
    $this->_init('outstocknotification/outstocknotification');
  }
	
  public function addDataNotify($isArray) {
    if ($isArray) {
      $pId   = $isArray['productid'];
      $website_id = Mage::app()->getStore()->getWebsiteId();
      if (isset($isArray['firstname'])) {
        $fName = $isArray['firstname'];
      }
      else {
        $fName = 'Guest';
      }
      if (isset($isArray['lastname'])) {
        $lName  = $isArray['lastname'];
      }
      else {
        $lName = 'Guest';
      }

      $email = $isArray['email'];
      $customer_id = $isArray['customer_id'];      
      $alertModel = Mage::getModel('productalert/stock')->getCollection()
				->addFieldToFilter('product_id', $pId)
				->addFieldToFilter('email', $email)
				->addFieldToFilter('status', 0)
				->getData();
				
      if (!empty($alertModel)) {
        return array(
					'success' => false,
					'error' => true,					
					'message' => Mage::helper('outstocknotification')->__('You already subscribed to this product.')
				);
      } else {       
        $data = array('product_id' => $pId,
					'website_id' => $website_id,
					'add_date' => now(),
					'email' => $email,
					'firstname' => $fName, 
					'lastname' => $lName,
					'customer_id' => $customer_id
				);
				
        $model = Mage::getModel('productalert/stock')->setData($data);      
        try{
          $model->save();
          return array(
						'success' => true
					);
        } catch (Exception $e) {         
					return array(
						'success' => false,
						'error' => true
					);
				}  
      }			
    }
    else {
      return false;
    }
  }
}