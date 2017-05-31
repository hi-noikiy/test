<?php
namespace Ktpl\Repaircenter\Model;
class Repairtocustomer extends \Magento\Framework\Model\AbstractModel implements  \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'repair_customer';

    protected function _construct()
    {
        $this->_init('Ktpl\Repaircenter\Model\ResourceModel\Repairtocustomer');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
