<?php
namespace Ktpl\Customreport\Model;
class Salesinvoicevat extends \Magento\Framework\Model\AbstractModel implements  \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'invoice_vat';

    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\ResourceModel\Salesinvoicevat');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
