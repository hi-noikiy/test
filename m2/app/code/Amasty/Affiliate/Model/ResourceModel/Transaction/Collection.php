<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel\Transaction;

use Amasty\Affiliate\Model\Transaction as TransactionModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transaction_id';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Affiliate\Api\AccountRepositoryInterface
     */
    protected $accountRepository;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Api\AccountRepositoryInterface $accountRepository,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->scopeConfig = $scopeConfig;
        $this->accountRepository = $accountRepository;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Transaction', 'Amasty\Affiliate\Model\ResourceModel\Transaction');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['sales_order' => $this->getTable('sales_order')],
                'main_table.order_increment_id = sales_order.increment_id',
                [
                    'customer_account_id' => 'customer_id',
                    'increment_id', 'customer_email',
                    'base_grand_total', 'store_id',
                    'order_id' => 'entity_id',
                    'base_subtotal'
                ]
            )->joinLeft(
                ['affiliate_account' => $this->getTable('amasty_affiliate_account')],
                'main_table.affiliate_account_id = affiliate_account.account_id',
                ['customer_id']
            )->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'customer.entity_id = affiliate_account.customer_id',
                ['email', 'firstname', 'lastname']
            )->distinct();

        return $this;
    }

    public function addFilterByOrderIncrementId($incrementId)
    {
        $this->addFieldToFilter('order_increment_id', ['eq' => $incrementId]);

        return $this;
    }

    public function isOrderTransactionExists($incrementId)
    {
        $isOrderTransactionExists = false;

        if ($this->addFilterByOrderIncrementId($incrementId)->getSize() > 0) {
            $isOrderTransactionExists = true;
        }

        return $isOrderTransactionExists;
    }

    /**
     * @param $customerEmail
     * @param $programId
     * @return $this
     */
    public function addCustomerProgramFilter($customerEmail, $programId)
    {
        $this->addFieldToFilter('customer_email', $customerEmail);
        $this->addFieldToFilter('program_id', $programId);

        return $this;
    }

    /**
     * @return $this
     */
    public function addHoldFilter()
    {
        $this->addFieldToFilter('main_table.status', ['eq' => \Amasty\Affiliate\Model\Transaction::STATUS_ON_HOLD]);
        $onHoldDays = $this->scopeConfig->getValue('amasty_affiliate/commission/holding_period');
        $this->getSelect()->where('main_table.updated_at < NOW() - INTERVAL ? DAY', $onHoldDays);

        return $this;
    }

    /**
     * @param \Amasty\Affiliate\Model\Transaction $transaction
     * @return \Amasty\Affiliate\Model\Transaction
     */
    public function getPerProfitTransaction($transaction)
    {
        $this
            ->addFieldToFilter('main_table.updated_at', ['gt' => $transaction->getUpdatedAt()])
            ->addFieldToFilter('main_table.type', ['eq' => $transaction::TYPE_PER_PROFIT])
            ->addFieldToFilter('main_table.affiliate_account_id', ['eq' => $transaction->getAffiliateAccountId()])
            ->addFieldToFilter('main_table.program_id', ['eq' => $transaction->getProgramId()])
            ->setOrder('main_table.updated_at', 'ASC');
        ;

        return $this->getFirstItem();
    }

    /**
     * @return mixed
     */
    public function getProfit()
    {
        $this->getSelect()->columns(['subtotal' => 'SUM(base_subtotal)', 'discount' => 'SUM(base_discount_amount)']);

        $subtotal = $this->getFirstItem()->getSubtotal();
        $discount = $this->getFirstItem()->getDiscount();
        $profit = $subtotal + $discount;

        return $profit;
    }

    /**
     * @param $programId
     * @param $accountId
     * @return $this
     */
    public function addForFutureFilter($programId, $accountId)
    {
        $this->addFieldToFilter('program_id', ['eq' => $programId]);
        $this->addFieldToFilter('affiliate_account_id', ['eq' => $accountId]);
        $this->addFieldToFilter('type', ['eq' => \Amasty\Affiliate\Model\Transaction::TYPE_FOR_FUTURE_PER_PROFIT]);
        $this->addFieldToFilter(
            'main_table.status',
            ['eq' => \Amasty\Affiliate\Model\Transaction::STATUS_READY_FOR_PER_PROFIT]
        );

        return $this;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->getConnection()->update(
            $this->getResource()->getMainTable(),
            ['status' => $status]
        );

        return $this;
    }

    /**
     * @param $accountId
     * @return $this
     */
    public function addAccountIdFilter($accountId)
    {
        $this->addFieldToFilter('affiliate_account_id', ['eq' => $accountId]);

        return $this;
    }

    public function addIncrementIdFilter($incrementId)
    {
        $this->addFieldToFilter('order_increment_id', ['eq' => $incrementId]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addFrontTypeFilter()
    {
        $this->addFieldToFilter(
            'type',
            ['nin' =>
                [
                    TransactionModel::TYPE_FOR_FUTURE_PER_PROFIT
                ],
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    public function addCompletedFilter()
    {
        $this->addFieldToFilter('main_table.status', ['eq' => TransactionModel::STATUS_COMPLETED]);

        return $this;
    }

    /**
     * @return $this
     */
    public function addAscSorting()
    {
        return $this->setOrder('updated_at', 'ASC');
    }

    /**
     * @return $this
     */
    public function addDescSorting()
    {
        return $this->setOrder('updated_at', 'DESC');
    }

    public function addTypeFilter($type)
    {
        return $this->addFieldToFilter('type', ['eq' => $type]);
    }
}
