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

class AccountRepository extends AbstractRepository implements \Amasty\Affiliate\Api\AccountRepositoryInterface
{

    /**
     * @var ResourceModel\Account
     */
    private $resource;

    /**
     * @var AccountFactory
     */
    private $factory;

    /**
     * @var array
     */
    private $accountById = [];

    /**
     * @var array
     */
    private $accountByCustomerId = [];

    /**
     * @var array
     */
    private $accountByCouponCode = [];

    /**
     * @var array
     */
    private $accountByRefferingCode = [];

    /**
     * @var \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var ResourceModel\Account\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResourceModel\Coupon\CollectionFactory
     */
    private $couponCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var int
     */
    private $currentCustomerId;

    /**
     * AccountRepository constructor.
     * @param \Amasty\Affiliate\Model\ResourceModel\Account $resource
     * @param \Amasty\Affiliate\Model\AccountFactory $factory
     * @param \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory
     * @param \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Amasty\Affiliate\Model\ResourceModel\Account $resource,
        \Amasty\Affiliate\Model\AccountFactory $factory,
        \Magento\Eav\Api\Data\AttributeSearchResultsInterfaceFactory $searchResultsFactory,
        \Amasty\Affiliate\Model\ResourceModel\Account\CollectionFactory $collectionFactory,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\CollectionFactory $couponCollectionFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resource = $resource;
        $this->factory = $factory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->couponCollectionFactory = $couponCollectionFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * {@inheritdoc}
     */
    public function save(Data\AccountInterface $entity)
    {
        if ($entity->getAccountId()) {
            $oldStatus = $this->get($entity->getAccountId())->getIsAffiliateActive();
            $entity = $this->get($entity->getAccountId())->addData($entity->getData());
            $newStatus = $entity->getIsAffiliateActive();
            if ($oldStatus != $newStatus) {
                $entity->sendAffiliateStatusEmail($newStatus);
            }
        }

        try {
            $this->resource->save($entity);
            unset($this->accountById[$entity->getAccountId()]);
        } catch (\Exception $e) {
            if ($entity->getAccountId()) {
                throw new CouldNotSaveException(
                    __('Unable to save account with ID %1. Error: %2', [$entity->getAccountId(), $e->getMessage()])
                );
            }
            throw new CouldNotSaveException(__('Unable to save new account. Error: %1', $e->getMessage()));
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (!isset($this->accountById[$id])) {
            /** @var \Amasty\Affiliate\Model\Account $entity */
            $entity = $this->resource->load($this->factory->create(), $id);
            if (!$entity->getAccountId()) {
                throw new NoSuchEntityException(__('Account with specified ID "%1" not found.', $id));
            }
            $this->accountById[$id] = $entity;
        }
        return $this->accountById[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Data\AccountInterface $entity)
    {
        try {
            $this->resource->delete($entity);
            unset($this->accountById[$entity->getId()]);
        } catch (\Exception $e) {
            if ($entity->getAccountId()) {
                throw new CouldNotDeleteException(
                    __('Unable to remove account with ID %1. Error: %2', [$entity->getAccountId(), $e->getMessage()])
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove account. Error: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isAffiliate($customerId = null)
    {
        $isAffiliate = false;

        if ($customerId == null) {
            $customerId = $this->getCurrentCustomerId();
        }

        /** @var Account $customer */
        $affiliate = $this->getByCustomerId($customerId);

        if ($affiliate->getAccountId()) {
            $isAffiliate = true;
        }

        return $isAffiliate;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCustomerId($customerId)
    {
        if (isset($this->accountByCustomerId[$customerId])) {
            return $this->accountByCustomerId[$customerId];
        }

        /** @var Account $customer */
        $account = $this->factory->create();
        $account->loadByCustomerId($customerId);
        if ($account->getCustomerId()) {
            $this->accountByCustomerId[$customerId] = $account;
            $this->accountById[$account->getAccountId()] = $account;
            $this->accountByRefferingCode[$account->getReferringCode()] = $account;
        }

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentAccount()
    {
        $customerId = $this->customerSession->getCustomerId();
        return $this->getByCustomerId($customerId);
    }

    /**
     * {@inheritdoc}
     */
    public function getByCouponCode($couponCode)
    {
        if (isset($this->accountByCouponCode[$couponCode])) {
            return $this->accountByCouponCode[$couponCode];
        }

        /** @var Account $customer */
        $account = $this->factory->create();
        $account->loadByCouponCode($couponCode);

        if (!$account->getAccountId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'code',
                        'fieldValue' => $couponCode
                    ]
                )
            );
        } else {
            $this->accountByCustomerId[$account->getCustomerId()] = $account;
            $this->accountByCouponCode[$couponCode] = $account;
            $this->accountById[$account->getAccountId()] = $account;
            $this->accountByRefferingCode[$account->getReferringCode()] = $account;
        }

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function getByReferringCode($code)
    {
        if (isset($this->accountByRefferingCode[$code])) {
            return $this->accountByRefferingCode[$code];
        }

        /** @var Account $customer */
        $account = $this->factory->create();
        $account->loadByReferringCode($code);

        if (!$account->getAccountId()) {
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue',
                    [
                        'fieldName' => 'referring_code',
                        'fieldValue' => $code
                    ]
                )
            );
        } else {
            $this->accountByCustomerId[$account->getCustomerId()] = $account;
            $this->accountById[$account->getAccountId()] = $account;
            $this->accountByRefferingCode[$code] = $account;
        }

        return $account;
    }

    protected function getCurrentCustomerId()
    {
        if (!$this->currentCustomerId) {
            $this->currentCustomerId = $this->customerSession->getCustomerId();
        }

        return $this->currentCustomerId;
    }
}
