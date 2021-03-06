<?php
/**
 * Copyright © 2015 Collins Harper. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace CollinsHarper\CanadaPost\Model\ResourceModel\Returns;


use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Canada POst Shipment Link Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('CollinsHarper\CanadaPost\Model\Returns', 'CollinsHarper\CanadaPost\Model\ResourceModel\Returns');
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

}