<?php
namespace Ktpl\Customreport\Model\ResourceModel\Deliveryorder;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'delivery_id';
    const deliveryorder = 'sales_flat_deliveryorder';
    
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init('Ktpl\Customreport\Model\Deliveryorder','Ktpl\Customreport\Model\ResourceModel\Deliveryorder');
        
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
                ['secondTable' => $this->getTable('sales_order')],
                'main_table.order_id = secondTable.increment_id',
                ['entity_id','subtotal','order_currency_code','total_qty_ordered', 'created_at']
            );
    }
    
}
