<?php 
namespace ClassyLlama\LlamaCoin\Model\ResourceModel\Creditcard;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    
    protected function _construct()
    {
        $this->_init(ClassyLlama\LlamaCoin\Model\Creditcard::class, ClassyLlama\LlamaCoin\Model\ResourceModel\Creditcard::class);
    }

    
}
