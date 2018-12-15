<?php

class Ktpl_Eshopsync_Model_Order extends Webkul_Eshopsync_Model_Order
{
    /*
     * Disallow sync of order based on customer group.
     * @param type $client
     * @param type $order_id
     * @return boolean
    */
    public function exportSpecificOrder($client, $order_id){
    	$This_order = Mage::getModel('sales/order')->load($order_id);
        
      $cust_group = Mage::getStoreConfig('eshopsync/auto/group');
      $cust_group_id = $This_order->getCustomerGroupId();
              
      if (!in_array($cust_group_id, explode(',',$cust_group))) {
          return false;
      }
      
      return parent::exportSpecificOrder($client, $order_id);	
    }
    
 }