<?php
namespace Ktpl\Customreport\Model\ResourceModel\Wholesaler;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'wholesaler_id';
    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\Wholesaler','Ktpl\Customreport\Model\ResourceModel\Wholesaler');
    }
}
