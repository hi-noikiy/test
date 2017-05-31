<?php
namespace Ktpl\Repaircenter\Model\ResourceModel;
class Repairtocenter extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('repair_to_center','repair_id');
    }
}
