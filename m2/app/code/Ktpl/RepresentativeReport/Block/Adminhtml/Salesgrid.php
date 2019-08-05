<?php

namespace Ktpl\RepresentativeReport\Block\Adminhtml;

class Salesgrid extends \Magento\Framework\View\Element\Template
{
    protected $_coreSession;
    protected $_orderFactory;
    
    /**
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\View\Element\Template\Context $contex
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession, 
        \Magento\Framework\View\Element\Template\Context $contex,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderFactory,   
        array $data = []
    ) {
        $this->_orderFactory = $orderFactory;
        $this->_coreSession = $coreSession;
        parent::__construct($contex,$data);
    }
    
    /**
     * 
     * @param type $from
     * @param type $to
     * @return collection
     */
    public function getcollects($from,$to) {
        
        $data = $this->_coreSession->getMyCustomData(); //$_POST;
        $status = $data['order_status'];
        $sr =  $data['sr'];
        $store =  $data['store_switcher'];
        
        $collection = null;
        $collection =$this->_orderFactory->create();
        $collection->getSelect()->joinLeft(array('salesrep' => $collection->getTable("salesrep")), 'salesrep.order_id=main_table.entity_id');

        if (isset($status)) {
            $collection->addAttributeToFilter('main_table.status', array('in' => $status));
        }

        if (isset($sr) ) {
          $collection->addAttributeToFilter('salesrep.rep_id', array('in' => $sr));
        }
        if ($store && isset($store) && $store!='' ) {
          $collection->addAttributeToFilter('main_table.store_id', array('eq' => $store));
        }
       
        return $collection->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to));
    }
    
}
