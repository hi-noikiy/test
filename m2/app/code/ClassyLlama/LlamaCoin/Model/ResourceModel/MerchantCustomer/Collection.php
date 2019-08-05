<?php 
namespace ClassyLlama\LlamaCoin\Model\ResourceModel\MerchantCustomer;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'merchant_customer_id';
    
    protected function _construct()
    {
        $this->_init(ClassyLlama\LlamaCoin\Model\MerchantCustomer::class, ClassyLlama\LlamaCoin\Model\ResourceModel\MerchantCustomer::class);
    }

    
}
