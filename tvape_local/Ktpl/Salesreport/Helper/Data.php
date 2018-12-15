<?php

class Ktpl_Salesreport_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getcollect() {
        $session = Mage::getSingleton('core/session');
        $data = $session->getMyCustomData();
        $status = $data['order_status'];
        $sr = $data['sr'];
        $store = $data['store_switcher'];
        
        $collection = Mage::getResourceModel('sales/order_item_collection');
     
        $collection->getSelect()->joinLeft(array('salesord' => $collection->getTable("sales/order")), 'main_table.order_id=salesord.entity_id');
        $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable("salesrep/salesrep")), 'salesrep.order_id=salesord.entity_id');
        if (isset($status)) {
            $collection->addAttributeToFilter('salesord.status', array('in' => $status));
        }

        if (isset($sr)) {
            $collection->addAttributeToFilter('salesrep.rep_id', array('in' => $sr));
        }
        if (isset($store) && $store != '') {
            $collection->addAttributeToFilter('salesord.store_id', array('eq' => $store));
        }
        
        return $collection;
    }

}


