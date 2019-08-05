<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model;

use Amasty\Affiliate\Api\Data\ProgramInterface;
use Magento\SalesRule\Model\Rule;

/**
 * Class Program
 * @method \Amasty\Affiliate\Model\ResourceModel\Program _getResource()
 * @package Amasty\Affiliate\Model
 */
class Program extends \Magento\Rule\Model\AbstractModel implements ProgramInterface
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    const COMMISSION_TYPE_FIXED = 'fixed';
    const COMMISSION_TYPE_PERCENT = 'percent';

    /**
     * @var \Magento\SalesRule\Model\Rule\Condition\CombineFactory
     */
    private $combineFactory;

    /**
     * @var \Magento\SalesRule\Model\Rule\Action\CollectionFactory
     */
    private $actionCollectionFactory;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\Affiliate\Api\TransactionRepositoryInterface
     */
    private $transactionRepository;

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var ResourceModel\Transaction\CollectionFactory
     */
    private $transactionsCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ResourceModel\Coupon\Collection
     */
    private $couponCollection;

    /**
     * Program constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param Rule\Condition\CombineFactory $combineFactory
     * @param Rule\Action\CollectionFactory $actionCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Locale\CurrencyInterface $localeCurrency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository
     * @param TransactionFactory $transactionFactory
     * @param ResourceModel\Transaction\CollectionFactory $transactionsCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param ResourceModel\Coupon\Collection $couponCollection
     * @param null $resource
     * @param null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\SalesRule\Model\Rule\Condition\CombineFactory $combineFactory,
        \Magento\SalesRule\Model\Rule\Action\CollectionFactory $actionCollectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Affiliate\Api\TransactionRepositoryInterface $transactionRepository,
        \Amasty\Affiliate\Model\TransactionFactory $transactionFactory,
        \Amasty\Affiliate\Model\ResourceModel\Transaction\CollectionFactory $transactionsCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Affiliate\Model\ResourceModel\Coupon\Collection $couponCollection,
        $resource = null,
        $resourceCollection = null,
        array $data = []
    ) {
        $this->combineFactory = $combineFactory;
        $this->actionCollectionFactory = $actionCollectionFactory;
        $this->localeCurrency = $localeCurrency;
        $this->storeManager = $storeManager;
        $this->transactionRepository = $transactionRepository;
        $this->transactionFactory = $transactionFactory;
        $this->transactionsCollectionFactory = $transactionsCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->couponCollection = $couponCollection;
        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Affiliate\Model\ResourceModel\Program');
        $this->setIdFieldName('program_id');
    }

    /**
     * Prepare commission values in dependency on type
     */
    public function preparePrices()
    {
        $this->preparePriceValues($this->getCommissionValueType(), $this->getCommissionValue(), 'commission_value');
        $this->preparePriceValues(
            $this->getCommissionTypeSecond(),
            $this->getCommissionValueSecond(),
            'commission_value_second'
        );
        $this->preparePriceValues($this->getDiscountType(), $this->getBaseDiscountAmount(), 'discount_amount');
    }

    /**
     * Add format and currency to price
     * @param $type
     * @param $value
     * @param $valueType
     */
    protected function preparePriceValues($type, $value, $valueType)
    {
        $store = $this->storeManager->getStore();
        $currency = $this->localeCurrency->getCurrency($store->getBaseCurrencyCode());

        if (isset($type)) {
            if ($type == self::COMMISSION_TYPE_FIXED) {
                $this->setData($valueType, $currency->toCurrency(sprintf("%f", $value)));
            } else {
                $this->setData($valueType, number_format($this->getData($valueType), 2) . '%');
            }
        }
    }

    /**
     * @param $order
     * @param string $status
     */
    public function addTransaction($order, $status = Transaction::STATUS_PENDING)
    {
        $this->_registry->register(\Amasty\Affiliate\Model\RegistryConstants::CURRENT_ORDER, $order, true);

        /** @var Transaction $transaction */
        $transaction = $this->transactionRepository->getByOrderProgramIds(
            $order->getIncrementId(),
            $this->getProgramId()
        );

        $type = Transaction::TYPE_PER_SALE;
        $onHoldPeriod = $this->scopeConfig->getValue('amasty_affiliate/commission/holding_period');
        if ($transaction->getTransactionId()) {//changing of existing transaction
            if ($onHoldPeriod <= 0) {
                $transaction->complete();
            } else {
                $transaction->onHold();
            }
        } else {//after place order, new transaction; not create for coupons transactions
            $isAffiliateCoupon = false;
            if ($order->getCouponCode() && $this->couponCollection->isAffiliateCoupon($order->getCouponCode())) {
                $isAffiliateCoupon = true;
            }
            if (!$this->transactionsCollectionFactory->create()->isOrderTransactionExists($order->getIncrementId())
                || !$isAffiliateCoupon
            ) {
                if ($this->getWithdrawalType() == Transaction::TYPE_PER_PROFIT) {
                    $type = Transaction::TYPE_FOR_FUTURE_PER_PROFIT;
                }
                $transaction->newTransaction($this, $order, $status, $type);
            }
        }
    }

    /**
     * @return array
     */
    public function getAvailableDiscountTypes()
    {
        return [
            Rule::BY_PERCENT_ACTION => __('Percent of product price discount'),
            Rule::BY_FIXED_ACTION => __('Fixed amount discount'),
            Rule::CART_FIXED_ACTION => __('Fixed amount discount for whole cart'),
            Rule::BUY_X_GET_Y_ACTION => __('Buy X get Y free (discount amount is Y)')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getConditionsInstance()
    {
        return $this->combineFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getActionsInstance()
    {
        return $this->actionCollectionFactory->create();
    }

    /**
     * Prepare program's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * @return array
     */
    public function getAvailableCommissionTypes()
    {
        return [self::COMMISSION_TYPE_PERCENT => __('Percent'), self::COMMISSION_TYPE_FIXED => __('Fixed')];
    }

    /**
     * @return array
     */
    public function getAvailableWithdrawalTypes()
    {
        return [Transaction::TYPE_PER_PROFIT => __('Per Profit'), Transaction::TYPE_PER_SALE => __('Per Sale')];
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramId()
    {
        return $this->_getData(ProgramInterface::PROGRAM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProgramId($programId)
    {
        $this->setData(ProgramInterface::PROGRAM_ID, $programId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRuleId()
    {
        return $this->_getData(ProgramInterface::RULE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuleId($ruleId)
    {
        $this->setData(ProgramInterface::RULE_ID, $ruleId);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_getData(ProgramInterface::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->setData(ProgramInterface::NAME, $name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getWithdrawalType()
    {
        return $this->_getData(ProgramInterface::WITHDRAWAL_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setWithdrawalType($withdrawalType)
    {
        $this->setData(ProgramInterface::WITHDRAWAL_TYPE, $withdrawalType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsActive()
    {
        return $this->_getData(ProgramInterface::IS_ACTIVE);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsActive($isActive)
    {
        $this->setData(ProgramInterface::IS_ACTIVE, $isActive);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionValue()
    {
        return $this->_getData(ProgramInterface::COMMISSION_VALUE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionValue($commissionValue)
    {
        $this->setData(ProgramInterface::COMMISSION_VALUE, $commissionValue);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionPerProfitAmount()
    {
        return $this->_getData(ProgramInterface::COMMISSION_PER_PROFIT_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionPerProfitAmount($commissionPerProfitAmount)
    {
        $this->setData(ProgramInterface::COMMISSION_PER_PROFIT_AMOUNT, $commissionPerProfitAmount);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionValueType()
    {
        return $this->_getData(ProgramInterface::COMMISSION_VALUE_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionValueType($commissionValueType)
    {
        $this->setData(ProgramInterface::COMMISSION_VALUE_TYPE, $commissionValueType);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromSecondOrder()
    {
        return $this->_getData(ProgramInterface::FROM_SECOND_ORDER);
    }

    /**
     * {@inheritdoc}
     */
    public function setFromSecondOrder($fromSecondOrder)
    {
        $this->setData(ProgramInterface::FROM_SECOND_ORDER, $fromSecondOrder);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionTypeSecond()
    {
        return $this->_getData(ProgramInterface::COMMISSION_TYPE_SECOND);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionTypeSecond($commissionTypeSecond)
    {
        $this->setData(ProgramInterface::COMMISSION_TYPE_SECOND, $commissionTypeSecond);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCommissionValueSecond()
    {
        return $this->_getData(ProgramInterface::COMMISSION_VALUE_SECOND);
    }

    /**
     * {@inheritdoc}
     */
    public function setCommissionValueSecond($commissionValueSecond)
    {
        $this->setData(ProgramInterface::COMMISSION_VALUE_SECOND, $commissionValueSecond);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIsLifetime()
    {
        return $this->_getData(ProgramInterface::IS_LIFETIME);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsLifetime($isLifetime)
    {
        $this->setData(ProgramInterface::IS_LIFETIME, $isLifetime);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFrequency()
    {
        return $this->_getData(ProgramInterface::FREQUENCY);
    }

    /**
     * {@inheritdoc}
     */
    public function setFrequency($frequency)
    {
        $this->setData(ProgramInterface::FREQUENCY, $frequency);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalSales()
    {
        return $this->_getData(ProgramInterface::TOTAL_SALES);
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalSales($totalSales)
    {
        $this->setData(ProgramInterface::TOTAL_SALES, $totalSales);

        return $this;
    }
}
