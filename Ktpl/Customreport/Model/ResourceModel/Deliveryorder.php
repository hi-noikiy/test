<?php
namespace Ktpl\Customreport\Model\ResourceModel;
class Deliveryorder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_flat_deliveryorder','delivery_id');
    }
}
 