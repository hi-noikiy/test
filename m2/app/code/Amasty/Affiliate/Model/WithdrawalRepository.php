<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class WithdrawalRepository extends TransactionRepository implements \Amasty\Affiliate\Api\WithdrawalRepositoryInterface
{
    private $withdrawalFactory;

    private $withdrawalResource;

    public function __construct(
        ResourceModel\Withdrawal $withdrawalResource,
        WithdrawalFactory $withdrawalFactory,
        ResourceModel\Transaction $resource,
        TransactionFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        ResourceModel\Transaction\CollectionFactory $collectionFactory
    ) {
        $this->withdrawalResource = $withdrawalResource;
        $this->withdrawalFactory = $withdrawalFactory;
        parent::__construct($resource, $factory, $searchResultsFactory, $collectionFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Withdrawal $entity */
            $entity = $this->withdrawalResource->load($this->withdrawalFactory->create(), $id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException(__('Withdrawal with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }
}
