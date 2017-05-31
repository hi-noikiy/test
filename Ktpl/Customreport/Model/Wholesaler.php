<?php
namespace Ktpl\Customreport\Model;
class Wholesaler extends \Magento\Framework\Model\AbstractModel implements WholesalerInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'wholesaler';

    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\ResourceModel\Wholesaler');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
