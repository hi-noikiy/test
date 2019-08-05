<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Account;

use Aheadworks\StoreCredit\Api\Data\TransactionSearchResultsInterface;
use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\TransactionRepositoryInterface;
use Aheadworks\StoreCredit\Block\Html\Pager;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Block\Account\Dashboard;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Template\Context;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Aheadworks\StoreCredit\Model\Comment\CommentPoolInterface;

/**
 * Class Aheadworks\StoreCredit\Block\Customer\StoreCreditBalance\Account\Transaction
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Transaction extends Dashboard
{
    /**
     * @var TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var TransactionSearchResultsInterface
     */
    private $transactions;

    /**
     * @var CommentPoolInterface
     */
    private $commentPool;

    /**
     * @var PriceHelper
     */
    private $priceHelper;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param PriceHelper $priceHelper
     * @param TransactionRepositoryInterface $transactionRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param CommentPoolInterface $commentPool
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        PriceHelper $priceHelper,
        TransactionRepositoryInterface $transactionRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CommentPoolInterface $commentPool,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->priceHelper = $priceHelper;
        $this->transactionRepository = $transactionRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->commentPool = $commentPool;
    }

    /**
     *  {@inheritDoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var Pager $pager */
        $pager = $this->getLayout()->createBlock(
            Pager::class,
            'aw_sc_transaction.pager'
        );

        $this->searchCriteriaBuilder->setCurrentPage($pager->getCurrentPage());
        $this->searchCriteriaBuilder->setPageSize($pager->getLimit());

        if ($this->getTransactions()) {
            $pager->setSearchResults($this->getTransactions());
            $this->setChild('pager', $pager);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Retrieve transaction list
     *
     * @return TransactionSearchResultsInterface
     */
    public function getTransactions()
    {
        if (empty($this->transactions)) {
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId != null) {
                $this->searchCriteriaBuilder->addFilter(TransactionInterface::CUSTOMER_ID, $customerId);
                $this->sortOrderBuilder->setField(TransactionInterface::TRANSACTION_ID)->setDescendingDirection();
                $this->searchCriteriaBuilder->addSortOrder($this->sortOrderBuilder->create());
                $this->transactions = $this->transactionRepository->getList(
                    $this->searchCriteriaBuilder->create()
                );
            }
        }
        return $this->transactions;
    }

    /**
     * Retrieve renderer comment
     *
     * @param TransactionInterface $transaction
     * @return string
     */
    public function renderComment($transaction)
    {
        if ($commentInstance = $this->commentPool->get($transaction->getType())) {
            $commentLabel = $commentInstance->renderComment(
                $transaction->getEntities(),
                null,
                $transaction->getCommentToCustomerPlaceholder(),
                true,
                true
            );
        }
        if (empty($commentLabel)) {
            $commentLabel = $transaction->getCommentToCustomer();
        }
        return $commentLabel;
    }

    /**
     * Format date in short format
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Format balance in currency
     *
     * @param float $balance
     * @return string
     */
    public function balanceFormat($balance)
    {
        return $this->priceHelper->currency(
            $balance,
            true,
            false
        );
    }
}
