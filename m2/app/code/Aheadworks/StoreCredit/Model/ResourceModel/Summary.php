<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\ResourceModel;

use Aheadworks\StoreCredit\Model\Summary as SummaryModel;

/**
 * Class Aheadworks\StoreCredit\Model\ResourceModel\Summary
 */
class Summary extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     *  {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init('aw_sc_summary', 'summary_id');
    }

    /**
     * Load Store Credit summary by customer id
     *
     * @param  SummaryModel $summary
     * @param  int $customerId
     * @return \Aheadworks\StoreCredit\Model\ResourceModel\Summary
     */
    public function loadByCustomerId(SummaryModel $summary, $customerId)
    {
        return $this->load($summary, $customerId, SummaryModel::CUSTOMER_ID);
    }

    /**
     * Get id by customer id
     *
     * @param int $customerId
     * @return int
     */
    public function getIdByCustomerId($customerId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), 'summary_id')
            ->where('customer_id = :customer_id');

        $bind = [':customer_id' => (int)$customerId];

        return $connection->fetchOne($select, $bind);
    }
}
