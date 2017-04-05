<?php
namespace Ktpl\Ordercustomer\Model;
class Ordercustomer extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'ordercustomer';

    protected function _construct()
    {
        $this->_init('Ktpl\Ordercustomer\Model\ResourceModel\Ordercustomer');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
