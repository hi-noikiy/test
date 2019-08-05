<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\Service;

use Aheadworks\StoreCredit\Api\Data\CustomerStoreCreditDetailsInterfaceFactory;
use Aheadworks\StoreCredit\Api\Data\CustomerStoreCreditDetailsInterface;
use Aheadworks\StoreCredit\Api\Data\TransactionInterface;
use Aheadworks\StoreCredit\Api\CustomerStoreCreditManagementInterface;
use Aheadworks\StoreCredit\Api\TransactionManagementInterface;
use Aheadworks\StoreCredit\Model\Comment\CommentPoolInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Aheadworks\StoreCredit\Model\Source\TransactionType;
use Aheadworks\StoreCredit\Model\Sender;
use Aheadworks\StoreCredit\Model\Source\SubscribeStatus;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Aheadworks\StoreCredit\Model\Source\NotifiedStatus;
use Aheadworks\StoreCredit\Model\ResourceModel\Transaction\Relation\Entity\SaveHandler as TransactionEntitySaveHandler;
use Aheadworks\StoreCredit\Model\Source\Transaction\EntityType as TransactionEntityType;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Aheadworks\StoreCredit\Model\Service\CustomerStoreCreditManagement
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomerStoreCreditService implements CustomerStoreCreditManagementInterface
{
    /**
     * @var CustomerStoreCreditDetailsInterfaceFactory
     */
    private $customerStoreCreditDetailsFactory;

    /**
     * @var CustomerStoreCreditDetailsInterface[]
     */
    private $customerStoreCreditDetailsCache = [];

    /**
     * @var TransactionManagementInterface
     */
    private $transactionService;

    /**
     * @var SummaryService
     */
    private $storeCreditSummaryService;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CommentPoolInterface
     */
    private $commentPool;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Sender
     */
    private $sender;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var AuthSession
     */
    private $adminSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var int
     */
    private $customerId;

    /**
     * @param CustomerStoreCreditDetailsInterfaceFactory $customerStoreCreditDetailsFactory
     * @param TransactionManagementInterface $transactionService
     * @param SummaryService $storeCreditSummaryService
     * @param CustomerRepositoryInterface $customerRepository
     * @param CommentPoolInterface $commentPool
     * @param PriceCurrencyInterface $priceCurrency
     * @param Sender $sender
     * @param AuthSession $adminSession
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        CustomerStoreCreditDetailsInterfaceFactory $customerStoreCreditDetailsFactory,
        TransactionManagementInterface $transactionService,
        SummaryService $storeCreditSummaryService,
        CustomerRepositoryInterface $customerRepository,
        CommentPoolInterface $commentPool,
        PriceCurrencyInterface $priceCurrency,
        Sender $sender,
        AuthSession $adminSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->customerStoreCreditDetailsFactory = $customerStoreCreditDetailsFactory;
        $this->transactionService = $transactionService;
        $this->storeCreditSummaryService = $storeCreditSummaryService;
        $this->customerRepository = $customerRepository;
        $this->commentPool = $commentPool;
        $this->priceCurrency = $priceCurrency;
        $this->sender = $sender;
        $this->adminSession = $adminSession;
        $this->orderRepository = $orderRepository;
    }

    /**
     *  {@inheritDoc}
     */
    public function spendStoreCreditOnCheckout($customerId, $spendStoreCredit, $order, $websiteId)
    {
        if (null != $customerId && abs($spendStoreCredit) > 0) {
            $this->setCustomerId($customerId);
            $transactionType = TransactionType::STORE_CREDIT_USED_IN_ORDER;
            $createdByAdmin = empty($order->getRemoteIp());
            return $this->createTransaction(
                $spendStoreCredit,
                $this->getCommentToCustomer($transactionType)->renderComment([
                    TransactionEntityType::ORDER_ID => [
                        'entity_id' => $order->getEntityId(),
                        'entity_label' => $order->getIncrementId()
                    ]
                ]),
                $this->getCommentToCustomer($transactionType)->getLabel(),
                null,
                $websiteId,
                $transactionType,
                [
                    TransactionEntitySaveHandler::TRANSACTION_ENTITY_TYPE => [
                        [
                            'entity_type' => TransactionEntityType::ORDER_ID,
                            'entity_id' => $order->getEntityId(),
                            'entity_label' => $order->getIncrementId()
                        ]
                    ]
                ],
                $order->getStoreId(),
                $createdByAdmin
            );
        }
        return false;
    }

    /**
     *  {@inheritDoc}
     */
    public function refundToStoreCredit($customerId, $refundToStoreCredit, $orderId, $creditmemo, $websiteId)
    {
        if (null != $customerId && abs($refundToStoreCredit) > 0) {
            $this->setCustomerId($customerId);
            $transactionType = TransactionType::REFUND_BY_STORE_CREDIT;
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
            return $this->createTransaction(
                $refundToStoreCredit,
                $this->getCommentToCustomer($transactionType)->renderComment([
                    TransactionEntityType::ORDER_ID => [
                        'entity_id' => $order->getEntityId(),
                        'entity_label' => $order->getIncrementId()
                    ],
                    TransactionEntityType::CREDIT_MEMO_ID => [
                        'entity_id' => $creditmemo->getEntityId(),
                        'entity_label' => $creditmemo->getIncrementId()
                    ]
                ]),
                $this->getCommentToCustomer($transactionType)->getLabel(),
                null,
                $websiteId,
                $transactionType,
                [
                    TransactionEntitySaveHandler::TRANSACTION_ENTITY_TYPE => [
                        [
                            'entity_type' => TransactionEntityType::ORDER_ID,
                            'entity_id' => $order->getEntityId(),
                            'entity_label' => $order->getIncrementId()
                        ],
                        [
                            'entity_type' => TransactionEntityType::CREDIT_MEMO_ID,
                            'entity_id' => $creditmemo->getEntityId(),
                            'entity_label' => $creditmemo->getIncrementId()
                        ]
                    ]
                ],
                $creditmemo->getStoreId()
            );
        }
        return false;
    }

    /**
     *  {@inheritDoc}
     */
    public function reimbursedSpentStoreCredit($customerId, $refundToStoreCredit, $orderId, $creditmemo, $websiteId)
    {
        if (null != $customerId && abs($refundToStoreCredit) > 0) {
            $this->setCustomerId($customerId);
            $transactionType = TransactionType::REIMBURSE_OF_SPENT_STORE_CREDIT;
            try {
                $order = $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
            return $this->createTransaction(
                $refundToStoreCredit,
                $this->getCommentToCustomer($transactionType)->renderComment([
                    TransactionEntityType::ORDER_ID => [
                        'entity_id' => $order->getEntityId(),
                        'entity_label' => $order->getIncrementId()
                    ],
                    TransactionEntityType::CREDIT_MEMO_ID => [
                        'entity_id' => $creditmemo->getEntityId(),
                        'entity_label' => $creditmemo->getIncrementId()
                    ]
                ]),
                $this->getCommentToCustomer($transactionType)->getLabel(),
                null,
                $websiteId,
                $transactionType,
                [
                    TransactionEntitySaveHandler::TRANSACTION_ENTITY_TYPE => [
                        [
                            'entity_type' => TransactionEntityType::ORDER_ID,
                            'entity_id' => $order->getEntityId(),
                            'entity_label' => $order->getIncrementId()
                        ],
                        [
                            'entity_type' => TransactionEntityType::CREDIT_MEMO_ID,
                            'entity_id' => $creditmemo->getEntityId(),
                            'entity_label' => $creditmemo->getIncrementId()
                        ]
                    ]
                ],
                $creditmemo->getStoreId()
            );
        }
        return false;
    }

    /**
     *  {@inheritDoc}
     */
    public function reimbursedSpentStoreCreditOrderCancel($customerId, $refundToStoreCredit, $order, $websiteId)
    {
        if (null != $customerId && abs($refundToStoreCredit) > 0) {
            $this->setCustomerId($customerId);
            $transactionType = TransactionType::ORDER_CANCELED;
            return $this->createTransaction(
                $refundToStoreCredit,
                $this->getCommentToCustomer($transactionType)->renderComment([
                    TransactionEntityType::ORDER_ID => [
                        'entity_id' => $order->getEntityId(),
                        'entity_label' => $order->getIncrementId()
                    ]
                ]),
                $this->getCommentToCustomer($transactionType)->getLabel(),
                null,
                $websiteId,
                $transactionType,
                [
                    TransactionEntitySaveHandler::TRANSACTION_ENTITY_TYPE => [
                        [
                            'entity_type' => TransactionEntityType::ORDER_ID,
                            'entity_id' => $order->getEntityId(),
                            'entity_label' => $order->getIncrementId()
                        ]
                    ]
                ],
                $order->getStoreId()
            );
        }
        return false;
    }

    /**
     *  {@inheritDoc}
     */
    public function saveAdminTransaction($transactionData)
    {
        $customerId = $transactionData[TransactionInterface::CUSTOMER_ID];
        $balance = $transactionData[TransactionInterface::BALANCE];

        if (null != $customerId && abs($balance) > 0) {
            $this->setCustomerId($customerId);
            return $this->createTransaction(
                $balance,
                $transactionData[TransactionInterface::COMMENT_TO_CUSTOMER],
                null,
                $transactionData[TransactionInterface::COMMENT_TO_ADMIN],
                $transactionData[TransactionInterface::WEBSITE_ID]
            );
        }
        return false;
    }

    /**
     *  {@inheritDoc}
     */
    public function calculateSpendStoreCredit($customerId, $amount)
    {
        $customerSpendStoreCredit = 0;
        $customerStoreCreditBalance = $this->getCustomerStoreCreditBalance($customerId);

        if ($customerStoreCreditBalance > 0) {
            $customerSpendStoreCredit = $customerStoreCreditBalance;
            if ($customerStoreCreditBalance > $amount) {
                $customerSpendStoreCredit = $amount;
            }
        }
        return $customerSpendStoreCredit;
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerStoreCreditBalance($customerId)
    {
        $customerStoreCreditDetails = $this->getCustomerStoreCreditDetails($customerId);
        return $customerStoreCreditDetails->getCustomerStoreCreditBalance();
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerStoreCreditBalanceCurrency($customerId)
    {
        $customerStoreCreditDetails = $this->getCustomerStoreCreditDetails($customerId);
        return $customerStoreCreditDetails->getCustomerStoreCreditBalanceCurrency();
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerBalanceUpdateNotificationStatus($customerId)
    {
        $customerStoreCreditDetails = $this->getCustomerStoreCreditDetails($customerId);
        return $customerStoreCreditDetails->getCustomerBalanceUpdateNotificationStatus();
    }

    /**
     *  {@inheritDoc}
     */
    public function getCustomerStoreCreditDetails($customerId)
    {
        if (isset($this->customerStoreCreditDetailsCache[$customerId])) {
            return $this->customerStoreCreditDetailsCache[$customerId];
        }

        /** @var CustomerStoreCreditDetailsInterface $customerStoreCreditDetails **/
        $customerStoreCreditDetails = $this->customerStoreCreditDetailsFactory->create();
        $balance = $this->storeCreditSummaryService->getCustomerStoreCreditBalance($customerId);
        $balanceUpdateNotificationStatus = $this->storeCreditSummaryService
            ->getCustomerBalanceUpdateNotificationStatus($customerId);

        $customerStoreCreditDetails
            ->setCustomerStoreCreditBalance($balance)
            ->setCustomerStoreCreditBalanceCurrency(
                $this->priceCurrency->convertAndRound($balance)
            )->setCustomerBalanceUpdateNotificationStatus($balanceUpdateNotificationStatus);

        $this->customerStoreCreditDetailsCache[$customerId] = $customerStoreCreditDetails;

        return $customerStoreCreditDetails;
    }

    /**
     *  {@inheritDoc}
     */
    public function resetCustomer()
    {
        $this->customer = null;
    }

    /**
     * Set customer id
     *
     * @param  int $customerId
     * @return CustomerStoreCreditService
     */
    private function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    /**
     * Get customer id
     *
     * @return int
     */
    private function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Create transaction model
     *
     * @param  int $balance
     * @param  string $commentToCustomer
     * @param  string $commentToCustomerPlaceholder
     * @param  string $commentToAdmin
     * @param  int $websiteId
     * @param  int $transactionType
     * @param  array $arguments
     * @param  int $storeId
     * @param  bool $createdByAdmin
     * @return TransactionInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function createTransaction(
        $balance,
        $commentToCustomer = null,
        $commentToCustomerPlaceholder = null,
        $commentToAdmin = null,
        $websiteId = null,
        $transactionType = TransactionType::BALANCE_ADJUSTED_BY_ADMIN,
        $arguments = [],
        $storeId = null,
        $createdByAdmin = null
    ) {
        $result = false;
        try {
            $adminTrasactionTypes = [
                TransactionType::BALANCE_ADJUSTED_BY_ADMIN,
                TransactionType::ORDER_CANCELED,
                TransactionType::REFUND_BY_STORE_CREDIT,
                TransactionType::REIMBURSE_OF_SPENT_STORE_CREDIT
            ];
            $adminUserId = (in_array($transactionType, $adminTrasactionTypes) || $createdByAdmin)
                ? $this->getAdminUserId()
                : null;

            $result = $this->transactionService->createTransaction(
                $this->getCustomer(),
                $balance,
                $transactionType,
                $commentToCustomer,
                $commentToCustomerPlaceholder,
                $commentToAdmin,
                $websiteId,
                NotifiedStatus::NO,
                $arguments,
                $adminUserId
            );
            if ($result) {
                $customerId = $this->getCustomer()->getId();
                $this->resetStoreCreditDetailsCache($customerId);
                $balance = $this->getCustomerStoreCreditBalance($customerId);
                $this->transactionService->updateCurrentBalance($result->getTransactionId(), $balance);

                if ($this->getCustomerBalanceUpdateNotificationStatus($customerId) != SubscribeStatus::SUBSCRIBED) {
                    return $result;
                }

                $storeId = $storeId ? : $this->getCustomer()->getStoreId();
                $balance = $this->priceCurrency->format(
                    $this->getCustomerStoreCreditBalanceCurrency($customerId),
                    false,
                    PriceCurrencyInterface::DEFAULT_PRECISION,
                    $storeId
                );
                $notifiedStatus = $this->sender->sendUpdateBalanceNotification(
                    $transactionType,
                    $this->getCustomer(),
                    $commentToCustomer,
                    $balance,
                    $storeId
                );
                $this->transactionService->updateNotifiedStatus($result->getTransactionId(), $notifiedStatus);
            }
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $result;
    }

    /**
     * Reset Store Credit details cache
     *
     * @param int $customerId
     * @return void
     */
    private function resetStoreCreditDetailsCache($customerId)
    {
        if (isset($this->customerStoreCreditDetailsCache[$customerId])) {
            unset($this->customerStoreCreditDetailsCache[$customerId]);
        }
    }

    /**
     * Get current admin user id
     *
     * @return int
     */
    private function getAdminUserId()
    {
        $userId = null;
        if ($this->adminSession->getUser()) {
            $userId = $this->adminSession->getUser()->getUserId();
        }
        return $userId;
    }

    /**
     * Retrieve customer model
     *
     * @return CustomerInterface
     */
    private function getCustomer()
    {
        if (null == $this->customer) {
            $this->customer = $this->customerRepository->getById($this->getCustomerId());
        }
        return $this->customer;
    }

    /**
     * Retrieve comment to customer
     *
     * @param int $type
     * @return string
     */
    private function getCommentToCustomer($type)
    {
        return $this->commentPool->get($type);
    }
}
