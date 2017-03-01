<?php
namespace Ktpl\Customreport\Model\ResourceModel;
class Wholesaler extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('wholesaler','wholesaler_id');
    }
}
