<?php
namespace Ktpl\Ordercustomer\Model\ResourceModel;
class Ordercustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('ordercustomer','ordercustomer_id');
    }
}
