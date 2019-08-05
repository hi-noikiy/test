<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Celigo\Magento2NetSuiteConnector\Model\CeligoSalesOrder',
            'Celigo\Magento2NetSuiteConnector\Model\ResourceModel\CeligoSalesOrder'
        );
    }
}
