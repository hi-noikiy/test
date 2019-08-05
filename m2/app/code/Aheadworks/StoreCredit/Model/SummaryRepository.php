<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model;

use Aheadworks\StoreCredit\Api\SummaryRepositoryInterface;
use Aheadworks\StoreCredit\Api\Data\SummaryInterfaceFactory;
use Aheadworks\StoreCredit\Api\Data\SummaryInterface;
use Aheadworks\StoreCredit\Model\ResourceModel\Summary as SummaryResource;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Aheadworks\StoreCredit\Model\SummaryRepository
 */
class SummaryRepository implements SummaryRepositoryInterface
{
    /**
     * @var SummaryResource
     */
    private $resource;

    /**
     * @var SummaryInterfaceFactory
     */
    private $summaryFactory;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Summary[]
     */
    private $instances = [];

    /**
     * @var Summary[]
     */
    private $instancesById = [];

    /**
     * @param SummaryResource $resource
     * @param SummaryInterfaceFactory $summaryFactory
     * @param EntityManager $entityManager
     */
    public function __construct(
        SummaryResource $resource,
        SummaryInterfaceFactory $summaryFactory,
        EntityManager $entityManager
    ) {
        $this->resource = $resource;
        $this->summaryFactory = $summaryFactory;
        $this->entityManager = $entityManager;
    }

    /**
     *  {@inheritDoc}
     */
    public function get($customerId)
    {
        if (isset($this->instances[$customerId])) {
            return $this->instances[$customerId];
        }
        $id = $this->resource->getIdByCustomerId($customerId);

        if (!$id) {
            throw new NoSuchEntityException(__('Requested Store Credit summary doesn\'t exist'));
        }

        return $this->getById($id);
    }

    /**
     *  {@inheritDoc}
     */
    public function getById($id)
    {
        if (isset($this->instancesById[$id])) {
            return $this->instancesById[$id];
        }

        /** @var $summary Summary **/
        $summary = $this->create();
        $this->entityManager->load($summary, $id);

        if (!$summary->getSummaryId()) {
            throw new NoSuchEntityException(__('Requested Store Credit summary doesn\'t exist'));
        }

        $this->instances[$summary->getCustomerId()] = $summary;
        $this->instancesById[$summary->getSummaryId()] = $summary;

        return $summary;
    }

    /**
     *  {@inheritDoc}
     */
    public function create()
    {
        return $this->summaryFactory->create();
    }

    /**
     *  {@inheritDoc}
     */
    public function save(SummaryInterface $summary)
    {
        try {
            $this->entityManager->save($summary);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        $this->instances[$summary->getCustomerId()] = $summary;
        $this->instancesById[$summary->getSummaryId()] = $summary;
        return $summary;
    }

    /**
     *  {@inheritDoc}
     */
    public function delete(SummaryInterface $summary)
    {
        unset($this->instances[$summary->getCustomerId()]);
        unset($this->instancesById[$summary->getSummaryId()]);
        $this->entityManager->delete($summary);
        return true;
    }

    /**
     *  {@inheritDoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
