<?php
namespace Ktpl\Repaircenter\Model\ResourceModel;
class Repairtocustomer extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('repair_to_customer','repair_customer_id');
    }
}
