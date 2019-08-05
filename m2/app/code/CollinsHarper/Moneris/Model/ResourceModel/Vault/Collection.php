<?php
/**
 * Copyright © 2016 Collinsharper. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CollinsHarper\Moneris\Model\ResourceModel\Vault;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'vault_id';

    protected function _construct()
    {
        $this->_init('CollinsHarper\Moneris\Model\Vault', 'CollinsHarper\Moneris\Model\ResourceModel\Vault');
    }
}
