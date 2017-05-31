<?php
namespace Ktpl\Ordercustomer\Model\ResourceModel\Ordercustomer;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'ordercustomer_id';
    const ordercustomer = 'ordercustomer';
    
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init('Ktpl\Ordercustomer\Model\Ordercustomer','Ktpl\Ordercustomer\Model\ResourceModel\Ordercustomer');
        
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection,
            $resource
        );
        $this->storeManager = $storeManager;
    }
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
                ['sfp' => $this->getTable('sales_flat_pickuporder')],
                'main_table.increment_id = sfp.order_id && main_table.product_sku = sfp.sku',
                ['wholesale_price','qty','order_id']
            );
        $this->getSelect()->joinLeft(
                ['sfc' => $this->getTable('sales_flat_cimorder')],
                'main_table.increment_id = sfc.order_id',
                ['sfc.deposit']
            );
        $this->getSelect()->joinLeft(
                ['wh' => $this->getTable('wholesaler')],
                'sfp.wholesaler_id = wh.wholesaler_id',
                ['wh.name']
            );
          $this->getSelect()->distinct();
    }
}
