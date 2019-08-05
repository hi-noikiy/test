<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Affiliate
 */


namespace Amasty\Affiliate\Model\ResourceModel\Program;

class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /** @var string $_idFieldName */
    protected $_idFieldName = 'program_id';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $date;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->date = $date;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('Amasty\Affiliate\Model\Program', 'Amasty\Affiliate\Model\ResourceModel\Program');
    }

    /**
     * @param string $ruleIds
     * @return $this
     */
    public function getProgramsByRuleIds($ruleIds)
    {
        $cartRules = str_replace(' ', '', $ruleIds);
        $cartRules = explode(',', $cartRules);

        $this->addFieldToFilter('main_table.rule_id', ['in' => $cartRules]);

        return $this;
    }

    public function isProgramRule($ruleId)
    {
        $isProgramRule = false;

        if ($this->getProgramsByRuleIds($ruleId)->count() > 0) {
            $isProgramRule = true;
        }

        return $isProgramRule;
    }

    /**
     * @param int $isActive
     * @return $this
     */
    public function addActiveFilter($isActive = 1)
    {
        $this->addFieldToFilter('main_table.is_active', ['eq' => $isActive]);

        return $this;
    }

    /**
     * @param $programId
     * @return $this
     */
    public function addProgramIdFilter($programId)
    {
        $this->addFieldToFilter('program_id', ['eq' => $programId]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->joinLeft(
                ['salesrule' => $this->getTable('salesrule')],
                'main_table.rule_id = salesrule.rule_id',
                ['discount_amount', 'simple_action']
            );

        return $this;
    }
}
