<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Paysafe\Paysafe\Model\ResourceModel\Payment\Information;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
        	'Paysafe\Paysafe\Model\Payment\Information',
        	'Paysafe\Paysafe\Model\ResourceModel\Payment\Information'
        );
    }
}
