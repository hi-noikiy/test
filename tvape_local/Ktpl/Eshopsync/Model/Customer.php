<?php

class Ktpl_Eshopsync_Model_Customer extends Webkul_Eshopsync_Model_Customer {

    /**
     * Disallow sync of customers based on customer group.
     * @param type $client
     * @param type $customer_id
     * @param type $action
     * @return boolean
     */
    public function syncSpecificCustomer($client, $customer_id, $action = "Export") {
        $customer = Mage::getModel('customer/customer')->load($customer_id);

        $cust_group = Mage::getStoreConfig('eshopsync/auto/group');
        $cust_group_id = $customer->getGroupId();

        if (!in_array($cust_group_id, explode(',', $cust_group)))
            return false;   
        
        return parent::syncSpecificCustomer($client, $customer_id, $action);
    }

}
