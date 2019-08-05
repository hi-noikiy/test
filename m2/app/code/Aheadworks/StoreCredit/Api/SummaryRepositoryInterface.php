<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Api;

use Aheadworks\StoreCredit\Api\Data\SummaryInterface;

/**
 * @api
 */
interface SummaryRepositoryInterface
{
    /**
     * Retrieve store credit summary data by id
     *
     * @param  int $id
     * @return SummaryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Retrieve store credit summary data by customer id
     *
     * @param  int $customerId
     * @return SummaryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($customerId);

    /**
     * Create new instance
     *
     * @return SummaryInterface
     */
    public function create();

    /**
     * Save store credit summary data
     *
     * @param  SummaryInterface $storeCreditSummary
     * @return SummaryInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(SummaryInterface $storeCreditSummary);

    /**
     * Delete store credit summary by id
     *
     * @param  int $id
     * @return boolean
     */
    public function deleteById($id);

    /**
     * Delete store credit summary data
     *
     * @param  SummaryInterface $storeCreditSummary
     * @return boolean
     */
    public function delete(SummaryInterface $storeCreditSummary);
}
