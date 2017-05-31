<?php
namespace Ktpl\Repaircenter\Model;
class Repairtocenter extends \Magento\Framework\Model\AbstractModel implements  \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'repair';

    protected function _construct()
    {
        $this->_init('Ktpl\Repaircenter\Model\ResourceModel\Repairtocenter');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
