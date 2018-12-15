<?php

class Ktpl_Guestabandoned_Model_Observer
{
   
    public function refreshData(){
         $fromDate = Mage::getModel('core/date')->date('Y-m-d 00:00:00');
        $quotec = Mage::getModel('sales/quote')->getCollection()
                ->addFieldToFilter('main_table.created_at',array('from' => $fromDate, true))
		->addFieldToFilter('main_table.is_active',array('eq' => '1'));
        $quotec->getSelect()->joinleft('sales_flat_quote_address', 'main_table.entity_id = sales_flat_quote_address.quote_id && sales_flat_quote_address.address_type = "shipping"',array('telephone'));  
        $quotec->addFieldToFilter('sales_flat_quote_address.telephone',array('notnull' => true));
        
        $orderc = Mage::getModel('sales/order')->getCollection()
                ->addFieldToFilter('main_table.created_at',array('from' => $fromDate, true));
        
        foreach($quotec as $qc){
            foreach($orderc as $oc){
                if($qc->getCustomerEmail() == $oc->getCustomerEmail()){
                    $qc->setData('is_active', 0);
                    $qc->save();
                }
            }
        }
        
    }
}