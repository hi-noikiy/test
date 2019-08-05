<?php
namespace Ktpl\Ordercolumn\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Sales\Api\OrderItemRepositoryInterface;
 
class Quantity extends Column
{
 
    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_orderItemRepository;
 
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $criteria,
        OrderItemRepositoryInterface $orderItemRepository,
        array $components = [], array $data = [])
    {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_orderItemRepository = $orderItemRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
            $orderItem  = $this->getOrderItemByIncrementId($item["entity_id"]);
            $qty=''; 
             $ik=0;  
             foreach ($orderItem as $skuitem) {
                if($ik==0){
                    $qty.=(int)$skuitem->getQtyOrdered();
                }else{
                    $qty.=', '.(int)$skuitem->getQtyOrdered();
                }
                $ik++;
             }
             $item[$this->getData('name')] = $qty;
            }
        }
        return $dataSource;
    }

    public function getOrderItemByIncrementId($incrementId) {
        $this->_searchCriteria->addFilter('order_id', $incrementId);
 
        $orderItemResult = $this->_orderItemRepository->getList(
            $this->_searchCriteria->create()
        )->getItems();
 
        return $orderItemResult;
    }
}