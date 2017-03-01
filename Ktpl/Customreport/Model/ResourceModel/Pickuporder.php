<?php
namespace Ktpl\Customreport\Model\ResourceModel;
class Pickuporder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_flat_pickuporder','pickup_id');
    }
}
 