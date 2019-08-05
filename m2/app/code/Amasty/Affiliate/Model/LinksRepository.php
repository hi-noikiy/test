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
use Amasty\Affiliate\Model\ResourceModel\Links\Collection;
use Magento\Framework\Api\SortOrder;

class LinksRepository extends AbstractRepository implements \Amasty\Affiliate\Api\LinksRepositoryInterface
{

    /**
     * @var ResourceModel\Links
     */
    private $resource;

    /**
     * @var LinksFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $entities = [];

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ResourceModel\Links\CollectionFactory
     */
    private $collectionFactory;

    /**
     * LinksRepository constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Links $resource
     * @param \Amasty\Affiliate\Model\LinksFactory $factory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Links $resource,
        \Amasty\Affiliate\Model\LinksFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\Affiliate\Model\ResourceModel\Links\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\LinksInterface $entity)
    {
        if ($entity->getLinkId()) {
            $entity = $this->get($entity->getLinkId())->addData($entity->getData());
        }

        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getLinkId()]);
        } catch (\Exception $e) {
            if ($entity->getLinkId()) {
                throw new CouldNotSaveException(
                    __('Unable to save links with ID %1. Error: %2', [$entity->getLinkId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new links. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Links $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getLinkId()) {
                throw new NoSuchEntityException(__('Links with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\LinksInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getLinkId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove links with ID %1. Error: %2', [$entity->getLinkId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove links. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
