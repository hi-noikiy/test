<?php 
namespace ClassyLlama\LlamaCoin\Model\ResourceModel\Errorcode;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'code';
    
    protected function _construct()
    {
        $this->_init(ClassyLlama\LlamaCoin\Model\Errorcode::class, ClassyLlama\LlamaCoin\Model\ResourceModel\Errorcode::class);
    }

    
}
