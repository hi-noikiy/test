<?php
namespace Ktpl\Repaircenter\Model\ResourceModel\Repairtocenter;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'repair_id';
        
    protected function _construct()
    {
        $this->_init('Ktpl\Repaircenter\Model\Repairtocenter','Ktpl\Repaircenter\Model\ResourceModel\Repairtocenter');
    }
    
}
