<?php

namespace ClassyLlama\LlamaCoin\Model;

class MerchantCustomer extends \Magento\Framework\Model\AbstractModel
{
    
    protected function _construct() {
        $this->_init(\ClassyLlama\LlamaCoin\Model\ResourceModel\MerchantCustomer::class);
    }
    
}