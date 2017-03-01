<?php
namespace Ktpl\Customreport\Model\ResourceModel;
class Salesinvoicevat extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('sales_invoice_vat','invoice_vat_id');
    }
}
