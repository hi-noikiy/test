<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Api;

use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\Data\TransactionSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Retrieve transaction data by id
     *
     * @param  int $id
     * @return TransactionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Create transaction instance
     *
     * @return TransactionInterface
     */
    public function create();

    /**
     * Save transaction data
     *
     * @param TransactionInterface $transaction
     * @param array $arguments
     * @return TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(TransactionInterface $transaction, $arguments = []);

    /**
     * Retrieve transaction matching the specified criteria
     *
     * @param  SearchCriteriaInterface $criteria
     * @return TransactionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria);
}
