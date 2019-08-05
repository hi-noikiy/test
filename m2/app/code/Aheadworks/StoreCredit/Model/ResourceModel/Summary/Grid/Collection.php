<?php
/**
* Copyright 2016 aheadWorks. All rights reserved.
* See LICENSE.txt for license details.
*/

namespace Aheadworks\StoreCredit\Model\ResourceModel\Summary\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\Order;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;
use Aheadworks\StoreCredit\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\StoreCredit\Model\Source\SubscribeStatus;

/**
 * Class Aheadworks\StoreCredit\Model\ResourceModel\Summary\Grid\Collection
 */
class Collection extends SearchResult
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntityFactory $entityFactory
     * @param Logger $logger
     * @param FetchStrategy $fetchStrategy
     * @param EventManager $eventManager
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param string $mainTable
     * @param string $resourceModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        Config $config,
        StoreManagerInterface $storeManager,
        $mainTable,
        $resourceModel
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $mainTable,
            $resourceModel
        );
    }

    /**
     *  {@inheritDoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            ['main_table' => $this->getMainTable()],
            [
                'customer_id' => 'main_table.entity_id',
                'website_id' => 'main_table.website_id'
            ]
        );

        $this->addNameToSelect();
        $this->joinSummaryTable();
        $this->joinLifetimeSales();
        $this->joinBalanceUpdateNootificationStatus();
        $this->addFilterToMap('website_id', 'main_table.website_id');
        $this->addFilterToMap('balance_update_notification_status', 'buns.balance_update_notification_status');
        return $this;
    }

    /**
     * Add Customer first name and last name to select
     *
     * @return $this
     */
    private function addNameToSelect()
    {
        $connection = $this->getResource()->getConnection();
        $nameExpr = $connection->getConcatSql(['firstname', 'lastname'], ' ');

        $select = $this->getSelect();
        $select->columns(
            ['customer_name' => $nameExpr]
        );

        return $this;
    }

    /**
     * Join aw_sc_summary table
     *
     * @return $this
     */
    private function joinSummaryTable()
    {
        $connection = $this->getResource()->getConnection();
        $select = $this->getSelect();

        $select->joinLeft(
            ['scs' => $this->getTable('aw_sc_summary')],
            'scs.customer_id=main_table.entity_id',
            [
                'balance' => $connection->getIfNullSql('scs.balance', '0'),
                'earn',
                'spend'
            ]
        );
        return $this;
    }

    /**
     * Join balance update notification status
     *
     * @return $this
     */
    private function joinBalanceUpdateNootificationStatus()
    {
        $connection = $this->getResource()->getConnection();
        $cases = [];
        foreach ($this->storeManager->getWebsites() as $website) {
            $cases[$website->getId()] = $this->config->isSubscribeCustomersToNotificationsByDefault($website->getId())
                ? SubscribeStatus::SUBSCRIBED
                : SubscribeStatus::NOT_SUBSCRIBED;
        }

        $select = $this->getConnection()->select()
            ->from(['ce' => $this->getTable('customer_entity')], ['entity_id'])
            ->joinLeft(
                ['scs' => $this->getTable('aw_sc_summary')],
                'scs.customer_id=ce.entity_id',
                [
                    'balance_update_notification_status' => $connection->getIfNullSql(
                        'scs.balance_update_notification_status',
                        $connection->getCaseSql('ce.website_id', $cases)
                    )
                ]
            );

        $this->getSelect()->joinLeft(
            ['buns' => $select],
            'buns.entity_id=main_table.entity_id',
            [
                'balance_update_notification_status' => 'buns.balance_update_notification_status'
            ]
        );
        return $this;
    }

    /**
     * Join lifetime sales sub-query
     *
     * @return Collection
     */
    private function joinLifetimeSales()
    {
        $select = $this->getSelect();
        $connection = $this->getResource()->getConnection();

        $select->joinLeft(
            ['so' => $this->getLifetimeSalesSelect()],
            'so.customer_id=main_table.entity_id',
            [
                'lifetime_sales' => $connection->getIfNullSql('so.lifetime_sales', '0'),
            ]
        );
        return $this;
    }

    /**
     * Retrieve lifetime sales sub-query
     *
     * @return \Magento\Framework\DB\Select
     */
    private function getLifetimeSalesSelect()
    {
        $select = clone $this->getSelect();
        $select->reset();

        $columns = $this->getConnection()->describeTable($this->getTable('sales_order'));
        $lifetimeSales = 'SUM(IFNULL(base_total_invoiced, 0)) - SUM(IFNULL(base_total_refunded, 0))';
        $allowedExternalColumns = [
            'base_aw_store_credit_refunded' => '+',
            'base_aw_reward_points_refund' => '+'
        ];

        foreach ($allowedExternalColumns as $allowedExternalColumn => $operation) {
            if (isset($columns[$allowedExternalColumn])) {
                $lifetimeSales .= $operation . 'SUM(IFNULL(' . $allowedExternalColumn . ', 0))';
            }
        }

        $select->from(
            $this->getTable('sales_order'),
            [
                'lifetime_sales' => new \Zend_Db_Expr('(' . $lifetimeSales . ')'),
                'customer_id',
            ]
        );

        $select->where('customer_id IS NOT NULL');
        $select->where(
            'state IN (?)',
            [Order::STATE_COMPLETE, Order::STATE_CLOSED, Order::STATE_PROCESSING]
        );

        $select->group('customer_id');

        return $select;
    }

    /**
     * Change filter for customer_name column
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'customer_name') {
            $field = ['firstname', 'lastname'];
            $condition = [$condition, $condition];
        } elseif (in_array($field, ['lifetime_sales', 'balance', 'earn', 'spend'])) {
            if (key($condition) == 'lteq') {
                $field = [$field, $field];
                $condition = [['null' => null], $condition];
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
