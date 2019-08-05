<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\TransactionInterface;
use Amasty\Affiliate\Api\ProgramRepositoryInterface;
use Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory;

class Transaction extends \Magento\Framework\Model\AbstractModel implements TransactionInterface
{
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELED = 'canceled';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_READY_FOR_PER_PROFIT = 'ready_for_per_profit';

    const TYPE_PER_PROFIT = 'per_profit';
    const TYPE_PER_SALE = 'per_sale';
    const TYPE_WITHDRAWAL = 'withdrawal';
    const TYPE_FOR_FUTURE_PER_PROFIT = 'for_future_per_profit';

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * @var \Amasty\Affiliate\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ProgramRepositoryInterface
     */
    protected $programRepository;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Amasty\Affiliate\Model\Program
     */
    protected $_program;

    protected $_currentProfit;

    protected $_ordersProfit;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var Mailsender
     */
    protected $mailsender;

    /**
     * @var ResourceModel\Coupon
     */
    protected $coupon;

    /**
     * @var ResourceModel\Coupon\Collection
     */
    protected $couponCollection;

    /**
     * @var \Amasty\Affiliate\Api\WithdrawalRepositoryInterface
     */
    protected $withdrawalRepository;

    /**
     * Transaction constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param ProgramRepositoryInterface $programRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param TransactionFactory $transactionFactory
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param Mailsender $mailsender
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Amasty\Affiliate\Api\ProgramRepositoryInterface $programRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Model\TransactionFactory $transactionFactory,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Amasty\Affiliate\Model\Mailsender $mailsender,
        \Amasty\Affiliate\Model\ResourceModel\Coupon $coupon,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection,
        \Amasty\Affiliate\Api\WithdrawalRepositoryInterface $withdrawalRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->transactionRepository = $transactionRepository;
        $this->accountRepository = $accountRepository;
        $this->scopeConfig = $scopeConfig;
        $this->programRepository = $programRepository;
        $this->transactionFactory = $transactionFactory;
        $this->cookieManager = $cookieManager;
        $this->mailsender = $mailsender;
        $this->coupon = $coupon;
        $this->couponCollection = $couponCollection;
        $this->withdrawalRepository = $withdrawalRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Affiliate\Model\ResourceModel\Transaction');
        $this->setIdFieldName('transaction_id');
    }

    /**
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_CANCELED => __('Canceled'),
            self::STATUS_ON_HOLD => __('On Hold')
        ];
    }

    /**
     * @return array
     */
    public function getAvailableTypes()
    {
        return [
            self::TYPE_PER_PROFIT => __('Per Profit'),
            self::TYPE_PER_SALE => __('Per Sale'),
            self::TYPE_WITHDRAWAL => __('Withdrawal')
        ];
    }

    /**
     * @return mixed
     */
    public function getPerProfitTransaction()
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $collection */
        $collection = $this->getResourceCollection();
        $perProfitTransaction = $collection->getPerProfitTransaction($this);

        return $perProfitTransaction;
    }

    /**
     * Check on hold transactions
     */
    public function completeHoldTransactions()
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactionCollection */
        $transactionCollection = $this->getResourceCollection();
        $transactionCollection->addHoldFilter();
        /** @var Transaction $transaction */
        foreach ($transactionCollection as $transaction) {
            $transaction->complete(true);
        }
    }

    /**
     * Complete transaction
     * @param bool $removeOnHold
     */
    public function complete($removeOnHold = false)
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->get($this->getAffiliateAccountId());

        $status = self::STATUS_COMPLETED;

        if ($this->getType() == self::TYPE_FOR_FUTURE_PER_PROFIT
            && $this->getCurrentProgram()->getWithdrawalType() == self::TYPE_PER_SALE
        ) {
            $this->setType(self::TYPE_PER_SALE);
        }

        if ($this->getType() == self::TYPE_FOR_FUTURE_PER_PROFIT) {
            $status = self::STATUS_READY_FOR_PER_PROFIT;
        } else {
            $account->setBalance($account->getBalance() + $this->getCommission());
            $account->setLifetimeCommission($account->getLifetimeCommission() + $this->getCommission());
            $account->setAvailable($account->getAvailable() + $this->getCommission());
        }

        $this->setStatus($status);
        $this->setBalance($account->getBalance());
        if ($removeOnHold) {
            $account->setOnHold($account->getOnHold() - $this->getCommission());
        }

        $this->transactionRepository->save($this);
        $this->accountRepository->save($account);
        if ($this->getType() == self::TYPE_FOR_FUTURE_PER_PROFIT
            && $this->getCurrentProgram()->getWithdrawalType() == self::TYPE_PER_PROFIT
        ) {
            $this->addCommissionByProfit();
        }
    }

    /**
     * Check if should add profit to account by profit and add it
     */
    public function addCommissionByProfit()
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $collection */
        $collection = $this->getResourceCollection();
        $collection->addForFutureFilter($this->getProgramId(), $this->getAffiliateAccountId());
        $profitCollection = clone $collection;
        $this->_ordersProfit = $profitCollection->getProfit();
        /** @var \Magento\Sales\Model\Order $currentOrder */
        $currentOrder = $this->_registry->registry(\Amasty\Affiliate\Model\RegistryConstants::CURRENT_ORDER);
        if (!$currentOrder->getEntityId()) {
            $this->_ordersProfit =
                $this->_ordersProfit + $currentOrder->getBaseSubtotal() + $currentOrder->getBaseDiscountAmount();
        }
        $program = $this->programRepository->get($this->getProgramId());
        /** @var \Amasty\Affiliate\Model\Coupon $profitCouponEntity */
        $profitCouponEntity = $this->coupon->getEntity($program->getProgramId(), $this->getAffiliateAccountId());
        $this->_currentProfit = $profitCouponEntity->getCurrentProfit();
        $allProfit = $this->_currentProfit + $this->_ordersProfit;
        if ($allProfit > $program->getCommissionPerProfitAmount()) {
            /** @var \Amasty\Affiliate\Model\Transaction $newTransaction */
            $newTransaction = $this->transactionFactory->create();
            $newTransaction->_ordersProfit = $this->_ordersProfit;
            $newTransaction->newTransaction(
                $program,
                null,
                self::STATUS_COMPLETED,
                self::TYPE_PER_PROFIT,
                $this->getAffiliateAccountId()
            );
            $newTransaction->complete();
            $collection->setStatus(self::STATUS_COMPLETED);
            $profitCouponEntity->setCurrentProfit($allProfit - $program->getCommissionPerProfitAmount());
            $this->coupon->save($profitCouponEntity);
        } else {
            if ($this->_ordersProfit !== null) {
                $this->setProfit($this->_ordersProfit);
                $this->transactionRepository->save($this);
            }
        }
    }

    /**
     * Put transaction on hold
     */
    public function onHold()
    {
        /** @var \Amasty\Affiliate\Model\Account $account */
        $account = $this->accountRepository->get($this->getAffiliateAccountId());

        $this->setStatus(Transaction::STATUS_ON_HOLD);
        $account->setOnHold($account->getOnHold() + $this->getCommission());

        $this->transactionRepository->save($this);
        $this->accountRepository->save($account);
    }

    /**
     * @param \Amasty\Affiliate\Model\Program $program
     * @param \Magento\Sales\Model\Order $order
     * @param $status
     * @param null $type
     * @param null $accountId
     * @return $this
     */
    public function newTransaction($program, $order, $status, $type = null, $accountId = null)
    {
        $this->_program = $program;
        $this->_order = $order;

        if ($this->_order !== null) {
            $couponCode = $this->_order->getCouponCode();
            if ($couponCode != null && $this->couponCollection->isAffiliateCoupon($couponCode)) {
                $account = $this->accountRepository->getByCouponCode($couponCode);
            } else {
                $affiliateCode = $this->cookieManager
                    ->getCookie(RegistryConstants::CURRENT_AFFILIATE_ACCOUNT_CODE);
                if ($affiliateCode != null) {
                    $account = $this->accountRepository->getByReferringCode($affiliateCode);
                } else {
                    return $this;
                }
            }
            $discount = $this->_order->getBaseDiscountAmount();
            $orderId = $this->_order->getIncrementId();
        } else {
            if ($accountId != null) {
                $account = $this->accountRepository->get($accountId);
                $discount = null;
                $orderId = null;
            } else {
                return $this;
            }
        }

        if ($program->getIsLifetime() && $this->_order !== null) {
            $customerLifetimeAccountId = $this->getCustomerLifetimeAccountId();
            if ($customerLifetimeAccountId !== null) {
                $account = $this->accountRepository->get($customerLifetimeAccountId);
            }
        }

        if (!$account->getIsAffiliateActive()) {
            return $this;
        }

        $commission = $this->calculateCommission();

        if ($type === null) {
            $type = $program->getWithdrawalType();
        }

        $data = [
            'affiliate_account_id' => $account->getAccountId(),
            'program_id' => $program->getProgramId(),
            'order_increment_id' => $orderId,
            'profit' => $this->_ordersProfit,
            'balance' => $account->getBalance(),
            'commission' => $commission,
            'discount' => $discount,
            'type' => $type,
            'status' => $status
        ];

        $this->setData($data);
        $this->transactionRepository->save($this);

        $this->accountRepository->save($account);

        if (in_array($this->getType(), [self::TYPE_PER_PROFIT, self::TYPE_PER_SALE])) {
            $this->sendEmail(Mailsender::TYPE_AFFILIATE_TRANSACTION_NEW);
        }

        return $this;
    }

    /**
     * Send email about transaction
     * @param $type
     */
    public function sendEmail($type)
    {
        if ($this->scopeConfig->getValue('amasty_affiliate/email/affiliate/' . $type)) {
            /** @var \Amasty\Affiliate\Model\Account $account */
            $account = $this->accountRepository->get($this->getAffiliateAccountId());
            if ($account->getReceiveNotifications()) {
                $emailData = $this->getData();
                $emailData['name'] = $account->getFirstname() . ' ' . $account->getLastname();

                $this->mailsender->sendAffiliateMail(
                    $emailData,
                    $type,
                    $account->getEmail(),
                    $account
                );
            }
        }
    }

    /**
     * @return int|mixed|null
     */
    protected function getCustomerLifetimeAccountId()
    {
        $customerLifetimeAccountId = null;

        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->getCustomerProgramTransactions();

        /** @var \Amasty\Affiliate\Model\Transaction $transaction */
        $transaction = $transactions->getFirstItem();

        if ($transaction->getAffiliateAccountId()) {
            $customerLifetimeAccountId = $transaction->getAffiliateAccountId();
        }

        return $customerLifetimeAccountId;
    }

    /**
     * @return ResourceModel\Transaction\Collection
     */
    protected function getCustomerProgramTransactions()
    {
        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->getResourceCollection();
        $transactions->addCustomerProgramFilter(
            $this->_order->getCustomerEmail(),
            $this->getCurrentProgram()->getProgramId()
        );

        return $transactions;
    }

    /**
     * @return float|int
     */
    protected function calculateCommission()
    {
        /** @var \Amasty\Affiliate\Model\Program $program */
        $program = $this->getCurrentProgram();

        $value = $program->getCommissionValue();
        $type = $program->getCommissionValueType();

        if ($program->getWithdrawalType() != self::TYPE_PER_PROFIT
            && $program->getFromSecondOrder()
            && $this->isSecondOrder()
        ) {
            $value = $program->getCommissionValueSecond();
            $type = $program->getCommissionTypeSecond();
        }

        if ($type == $program::COMMISSION_TYPE_PERCENT) {
            if ($program->getWithdrawalType() == self::TYPE_PER_PROFIT) {
                $value = $value / 100 * $program->getCommissionPerProfitAmount();
            } elseif ($this->_ordersProfit) {
                $value = ($value / 100) * $this->_ordersProfit;
            } else {
                $value = ($value / 100) * ($this->_order->getBaseSubtotal() + $this->_order->getBaseDiscountAmount());
            }
        }

        return $value;
    }

    /**
     * @return bool
     */
    protected function isSecondOrder()
    {
        $isSecondOrder = false;

        /** @var \Amasty\Affiliate\Model\ResourceModel\Transaction\Collection $transactions */
        $transactions = $this->getCustomerProgramTransactions($this->_order);

        if ($transactions->count() > 0) {
            $isSecondOrder = true;
        }

        return $isSecondOrder;
    }

    /**
     * @return \Amasty\Affiliate\Api\Data\ProgramInterface|Program
     */
    protected function getCurrentProgram()
    {
        if (!isset($this->_program)) {
            $this->_program = $this->programRepository->get($this->getProgramId());
        }

        return $this->_program;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransactionId()
    {
        return $this->_getData(TransactionInterface::TRANSACTION_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setTransactionId($transactionId)
    {
        $this->setData(TransactionInterface::TRANSACTION_ID, $transactionId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAffiliateAccountId()
    {
        return $this->_getData(TransactionInterface::AFFILIATE_ACCOUNT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAffiliateAccountId($affiliateAccountId)
    {
        $this->setData(TransactionInterface::AFFILIATE_ACCOUNT_ID, $affiliateAccountId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramId()
    {
        return $this->_getData(TransactionInterface::PROGRAM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProgramId($programId)
    {
        $this->setData(TransactionInterface::PROGRAM_ID, $programId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderIncrementId()
    {
        return $this->_getData(TransactionInterface::ORDER_INCREMENT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderIncrementId($orderIncrementId)
    {
        $this->setData(TransactionInterface::ORDER_INCREMENT_ID, $orderIncrementId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProfit()
    {
        return $this->_getData(TransactionInterface::PROFIT);
    }

    /**
     * {@inheritdoc}
     */
    public function setProfit($profit)
    {
        $this->setData(TransactionInterface::PROFIT, $profit);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance()
    {
        return $this->_getData(TransactionInterface::BALANCE);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalance($balance)
    {
        $this->setData(TransactionInterface::BALANCE, $balance);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommission()
    {
        return $this->_getData(TransactionInterface::COMMISSION);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommission($commission)
    {
        $this->setData(TransactionInterface::COMMISSION, $commission);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDiscount()
    {
        return $this->_getData(TransactionInterface::DISCOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscount($discount)
    {
        $this->setData(TransactionInterface::DISCOUNT, $discount);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->_getData(TransactionInterface::UPDATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->setData(TransactionInterface::UPDATED_AT, $updatedAt);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->_getData(TransactionInterface::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->setData(TransactionInterface::TYPE, $type);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->_getData(TransactionInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->setData('previous_status', $this->getStatus());
        $this->setData(TransactionInterface::STATUS, $status);

        return $this;
    }
}
