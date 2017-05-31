<?php
namespace Ktpl\Repaircenter\Model\ResourceModel\Repairtocustomer;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'repair_customer_id';
        
    protected function _construct()
    {
        $this->_init('Ktpl\Repaircenter\Model\Repairtocustomer','Ktpl\Repaircenter\Model\ResourceModel\Repairtocustomer');
    }
    
}
