<?php
namespace Ktpl\Customreport\Model;
class Pickuporder extends \Magento\Framework\Model\AbstractModel //implements PickuporderInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'pickup';

    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\ResourceModel\Pickuporder');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
