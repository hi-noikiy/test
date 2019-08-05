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
use Amasty\Affiliate\Model\ResourceModel\Banner\Collection;
use Magento\Framework\Api\SortOrder;

class BannerRepository extends AbstractRepository implements \Amasty\Affiliate\Api\BannerRepositoryInterface
{

    /**
     * @var ResourceModel\Banner
     */
    private $resource;

    /**
     * @var BannerFactory
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
     * @var ResourceModel\Banner\CollectionFactory
     */
    private $collectionFactory;

    /**
     * BannerRepository constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Banner $resource
     * @param \Amasty\Affiliate\Model\BannerFactory $factory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Banner\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Banner $resource,
        \Amasty\Affiliate\Model\BannerFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\Affiliate\Model\ResourceModel\Banner\CollectionFactory $collectionFactory
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\BannerInterface $entity)
    {
        if ($entity->getBannerId()) {
            /** @var \Amasty\Affiliate\Model\Banner $entity */
            $entity = $this->get($entity->getBannerId())->addData($entity->getData());
        }

        try {
            $this->resource->save($entity);
            unset($this->entities[$entity->getBannerId()]);
        } catch (\Exception $e) {
            if ($entity->getBannerId()) {
                throw new CouldNotSaveException(
                    __('Unable to save banner with ID %1. Error: %2', [$entity->getBannerId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new banner. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->entities[$id])) {
            /** @var \Amasty\Affiliate\Model\Banner $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getBannerId()) {
                throw new NoSuchEntityException(__('Banner with specified ID "%1" not found.', $id));
            }
            $this->entities[$id] = $entity;
        }
        return $this->entities[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\BannerInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->entities[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getBannerId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove banner with ID %1. Error: %2', [$entity->getBannerId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove banner. Error: %1', $e->getMessage()));
        }
        return true;
    }
}
