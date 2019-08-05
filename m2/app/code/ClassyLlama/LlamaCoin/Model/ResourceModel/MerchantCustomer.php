<?php

namespace ClassyLlama\LlamaCoin\Model\ResourceModel;

class MerchantCustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    protected function _construct() {
        $this->_init('merchant_customer', 'merchant_customer_id');
    }
    
}