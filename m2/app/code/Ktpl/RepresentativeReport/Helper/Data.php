<?php

namespace Ktpl\RepresentativeReport\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    
    protected $_coreSession;
    protected $_orderitem;
    
    /**
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderitemCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,   
        \Ktpl\RepresentativeReport\Model\ResourceModel\Order\Item\CollectionFactory $orderitemCollectionFactory    
    ) {
        $this->_coreSession = $coreSession;
        $this->_orderitem = $orderitemCollectionFactory;
    }
    
    /**
     * 
     * @return collection
     */
    public function getcollect() {
        $data = $this->_coreSession->getMyCustomData();
        $status = $data['order_status'];
        $sr = $data['sr'];
        $store = $data['store_switcher'];
        
        $collection = $this->getitemcollection();
     
        $collection->getSelect()->joinLeft(array('salesord' => $collection->getTable("sales_order")), 'main_table.order_id=salesord.entity_id');
        $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable("salesrep")), 'salesrep.order_id=salesord.entity_id');
        if (isset($status)) {
            $collection->addAttributeToFilter('salesord.status', array('in' => $status));
        }

        if (isset($sr)) {
            $collection->addAttributeToFilter('salesrep.rep_id', array('in' => $sr));
        }
        if ($store &&  isset($store) && $store != '') {
            $collection->addAttributeToFilter('salesord.store_id', array('eq' => $store));
        }
        
        return $collection;
    }
    
    /**
     * 
     * @return itemcollection
     */
    public function getitemcollection()
    {
        $itemcollection = $this->_orderitem->create();
        $itemcollection->addAttributeToSelect('*');
        return $itemcollection;
    }
    
}
