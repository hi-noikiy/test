<?php

class Ktpl_Customreport_Model_Mysql4_Salesinvoicevat extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('customreport/salesinvoicevat', 'invoice_vat_id');
    }
}