<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_MultiInventory
 */


namespace Amasty\MultiInventory\Plugin\Inventory\Model\ResourceModel\Product;

use Amasty\MultiInventory\Model\Dispatch;
use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
 use \Magento\CatalogInventory\Model\ResourceModel\Product\StockStatusBaseSelectProcessor as CatalogInventoryStockStatusBaseSelectProcessor;
 use \Magento\ConfigurableProduct\Model\ResourceModel\Product\StockStatusBaseSelectProcessor as ConfigurableProductStockStatusBaseSelectProcessor;

class StockStatusBaseSelectProcessorPlugin
{
    /**
     * @var \Amasty\MultiInventory\Helper\System
     */
    private $system;

    /**
     * @var \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item
     */
    private $warehouseStockResource;

    /**
     * @var Dispatch
     */
    private $dispatch;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfig;

    public function __construct(
        \Amasty\MultiInventory\Helper\System $system,
        \Amasty\MultiInventory\Model\ResourceModel\Warehouse\Item $warehouseStockResource,
        Dispatch $dispatch,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfig
    ) {
        $this->system = $system;
        $this->warehouseStockResource = $warehouseStockResource;
        $this->dispatch = $dispatch;
        $this->stockConfig = $stockConfig;
    }

    /**
     * @param BaseSelectProcessorInterface $subject
     * @param callable                                                                             $proceed
     * @param \Magento\Framework\DB\Select                                                         $select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function aroundProcess(
        BaseSelectProcessorInterface $subject,
        callable $proceed,
        \Magento\Framework\DB\Select $select
    ) {
        if (!$this->system->isMultiEnabled()
            || !$this->system->isLockOnStore()
            || ($this->stockConfig->isShowOutOfStock()
                && $subject instanceof CatalogInventoryStockStatusBaseSelectProcessor)
            || (!$this->stockConfig->isShowOutOfStock()
                && $subject instanceof ConfigurableProductStockStatusBaseSelectProcessor)
            //            || $select->getFlag('warehouse_index_joined')
        ) {
            return $proceed($select);
        }

        $this->dispatch->setCallables($this->system->getDispatchOrder());
        $this->dispatch->setDirection(Dispatch::DIRECTION_STORE);
        $this->dispatch->searchWh();
        $warehouseIds = $this->dispatch->getWarehousesRaw();
        if (empty($warehouseIds)) {
            return $proceed($select);
        }

        $productAlias = BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS;

        $joinCondition = $this->warehouseStockResource->getConnection()->quoteInto(
            sprintf('%1$s.entity_id = warehouse_index.product_id AND warehouse_index.warehouse_id = ?', $productAlias),
            current($warehouseIds)
        );

        $select->joinLeft(
            ['warehouse_index' => $this->warehouseStockResource->getMainTable()],
            $joinCondition,
            []
        );
        $select->joinLeft(
            ['stock' => $this->warehouseStockResource->getTable('cataloginventory_stock_status')],
            sprintf('stock.product_id = %s.entity_id', $productAlias),
            []
        )->where(
            '(stock.stock_status = ? OR warehouse_index.stock_status = ?)',
            \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
        );

        return $select;
    }
}
