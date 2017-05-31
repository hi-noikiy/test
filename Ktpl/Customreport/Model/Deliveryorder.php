<?php
namespace Ktpl\Customreport\Model;
class Deliveryorder extends \Magento\Framework\Model\AbstractModel implements DeliveryorderInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'deliveryorder';

    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\ResourceModel\Deliveryorder');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
