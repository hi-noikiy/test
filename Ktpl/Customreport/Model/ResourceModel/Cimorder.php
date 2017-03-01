<?php
namespace Ktpl\Customreport\Model\ResourceModel;
class Cimorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_flat_cimorder','cimorder_id');
    }
}
