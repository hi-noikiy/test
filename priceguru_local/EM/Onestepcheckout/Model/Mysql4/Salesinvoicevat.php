<?php

class EM_Onestepcheckout_Model_Mysql4_Salesinvoicevat extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('onestepcheckout/salesinvoicevat', 'invoice_vat_id');
    }
}