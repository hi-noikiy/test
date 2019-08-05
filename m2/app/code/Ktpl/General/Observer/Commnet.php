<?php
namespace Ktpl\General\Observer;
 
use Magento\Framework\Event\ObserverInterface;
 
class Commnet implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;
 
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    protected $orderFactory;
    protected $resourceConnection;
    protected $dateTimeFactory;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->orderFactory = $orderFactory;
        $this->resourceConnection = $resourceConnection;
         $this->dateTimeFactory = $dateTimeFactory;
    }
 
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $orderId = $observer->getEvent()->getDataObject()->getData('parent_id');
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');
        $dateModel = $this->dateTimeFactory->create();
        $date=$dateModel->gmtDate();
        
        $sql = "Update `".$tableName."` set updated_at = '".$date."' where entity_id = '".$orderId."'";
        $connection->query($sql); 

    }
}