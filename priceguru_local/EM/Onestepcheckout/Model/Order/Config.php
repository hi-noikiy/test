<?php

class EM_Onestepcheckout_Model_Order_Config extends Mage_Core_Model_Config_Base
{
    public function getStatuses()
    {
        return array(
            1 => Mage::helper('sales')->__('Yes'),
            0 => Mage::helper('sales')->__('No'),
        );
    }

    public function getWholesaler()
    {
    	$wArray = array();
    	$wdata = Mage::getSingleton('onestepcheckout/wholesaler')->getCollection();
    	foreach($wdata as $item) {
    		$wArray[$item->getId()] = $item->getName();
    	}
    	return $wArray;
    }
}
