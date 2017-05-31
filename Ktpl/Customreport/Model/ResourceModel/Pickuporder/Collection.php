<?php 
namespace Ktpl\Customreport\Model\ResourceModel\Pickuporder; 
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'pickup_id';
    const Pickuporder = 'sales_flat_pickuporder';
    protected $_request;    
    
    public function __construct(
            \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) { 
        $this->_init('Ktpl\Customreport\Model\Pickuporder','Ktpl\Customreport\Model\ResourceModel\Pickuporder');
        
        parent::__construct(
            $entityFactory, $logger, $fetchStrategy, $eventManager, $connection,
            $resource
        );
        $this->storeManager = $storeManager;
         $this->_request = $request;
         
    }
   

    
}
