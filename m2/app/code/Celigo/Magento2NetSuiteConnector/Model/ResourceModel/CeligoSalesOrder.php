<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Celigo\Magento2NetSuiteConnector\Model\ResourceModel;

/**
 * CeligoSalesOrder resource model
 *
 */
class CeligoSalesOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('celigo_sales_order', 'id');
    }
}
