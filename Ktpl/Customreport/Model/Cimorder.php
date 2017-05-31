<?php
namespace Ktpl\Customreport\Model;
class Cimorder extends \Magento\Framework\Model\AbstractModel implements CimorderInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'cim';

    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\ResourceModel\Cimorder');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
