<?php
namespace Ktpl\Customreport\Model\ResourceModel\Salesinvoicevat;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'invoice_vat_id';
        
    protected function _construct()
    {
        $this->_init('Ktpl\Customreport\Model\Salesinvoicevat','Ktpl\Customreport\Model\ResourceModel\Salesinvoicevat');
    }
    
}
