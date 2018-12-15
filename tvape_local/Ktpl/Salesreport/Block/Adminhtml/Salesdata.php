<?php

class Ktpl_Salesreport_Block_Adminhtml_Salesdata extends Mage_Adminhtml_Block_Abstract
{

  public function getcollects($from,$to) {
        $data = Mage::getSingleton('core/session')->getMyCustomData(); //$_POST;
        $status = $data['order_status'];
        $sr =  $data['sr'];
        $store =  $data['store_switcher'];
        
        $collection = null;
        $collection = Mage::getModel('sales/order')->getCollection();
        $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable("salesrep/salesrep")), 'salesrep.order_id=main_table.entity_id');

        if (isset($status)) {
            $collection->addAttributeToFilter('main_table.status', array('in' => $status));
        }

        if (isset($sr) ) {
          $collection->addAttributeToFilter('salesrep.rep_id', array('in' => $sr));
        }
        if (isset($store) && $store!='' ) {
          $collection->addAttributeToFilter('main_table.store_id', array('eq' => $store));
        }
       
        return $collection->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to));
    }
    
}