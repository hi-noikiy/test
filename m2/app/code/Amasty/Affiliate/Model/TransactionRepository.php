<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

class TransactionRepository extends AbstractRepository implements \Amasty\Affiliate\Api\TransactionRepositoryInterface
{

    /**
     * @var ResourceModel\Transaction
     */
    protected $resource;

    /**
     * @var TransactionFactory
     */
    protected $factory;

    /** @var array $entities */
    protected $entities = [];

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var ResourceModel\Transaction\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * TransactionRepository constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Transaction $resource
     * @param \Amasty\Affiliate\Model\TransactionFactory $factory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Transaction $resource,
        \Amasty\Affiliate\Model\TransactionFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\TransactionInterface $entity)
    {
        if ($entity->getTransactionId()) {
            $entity = $this->get($entity->getTransactionId())->addData($entity->getData());
        }

        try {
            if (($entity->getPreviousStatus() != null) && $entity->getPreviousStatus() != $entity->getStatus()) {
                $mailStatuses = [$entity::STATUS_COMPLETED, $entity::STATUS_CANCELED];
                if (in_array($entity->getStatus(), $mailStatuses)
                    && $entity->getType() != \Amasty\Affiliate\Model\Transaction::TYPE_WITHDRAWAL) {
                    $entity->sendEmail(Mailsender::TYPE_AFFILIATE_TRANSACTION_STATUS);
                }
            }

            $this->resource->save($entity);
            unset($this->entities[$entity->getTransactionId()]);
        } catch (\Exception $e) {
            if ($entity->getTransactionId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save transaction with ID %1. Error: %2',
                        [$entity->getTransactionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new transaction. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Transaction $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getTransactionId()) {
                throw new NoSuchEntityException(__('Transaction with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrderProgramIds($orderIncrementId, $programId)
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactionCollection */
        $transactionCollection = $this->collectionFactory->create();

        $transactionCollection
            ->addFieldToFilter('order_increment_id', $orderIncrementId)
            ->addFieldToFilter('program_id', $programId)
            ->setPageSize(1);

        $transaction = $this->factory->create();
        if ($transactionCollection->getSize() > 0) {
            $transaction = $this->get($transactionCollection->getFirstItem()->getTransactionId());
        }

        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\TransactionInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getTransactionId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove transaction with ID %1. Error: %2',
                        [$entity->getTransactionId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove transaction. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
