<?php

namespace Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Renderer;

class Position extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    protected $total_qty;
    protected $_coreSession;
    protected $_datetime;
    protected $griditem;
    
    /**
     * 
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Grid $griditem
     * @param \Ktpl\RepresentativeReport\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime, 
        \Ktpl\RepresentativeReport\Block\Adminhtml\Salesdata\Grid $griditem,    
        \Ktpl\RepresentativeReport\Helper\Data $helper    
    ) {
        $this->_coreSession = $coreSession;
        $this->_datetime = $datetime;
        $this->_helper = $helper;
        $this->griditem = $griditem;
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $row
     * @return type
     */
    public function render(\Magento\Framework\DataObject $row) {   
        $qty = $row->getData('order_quantity');
        $this->total_qty = $this->getcurrentqty();
        $a = number_format(($qty*100)/$this->total_qty,2);      
        $b = $this->getpreviousqty($row->getData('sku'));
        if($a=='nan'){ $a = 0;}
        if($b=='nan'){ $b = 0;}
        if($a > $b){
            return '<span style="color: green;">&uarr;'.($a - $b).'</span>';
        } else{
            return '<span style="color: grey;">&darr;'.($a - $b).'</span>';
        }
    }
    
    /**
     * @return type
     */
    public function getcurrentqty(){
        $coll =  $this->griditem->getitems(); 
        $tqty = 0;
        foreach($coll as $c){
            $tqty += $c->getData('order_quantity');
         }
         return $tqty;
    }
    
    /**
     * 
     * @param type $sku
     * @return type
     */
    public function getpreviousqty($sku){
        
        $data = $this->_coreSession->getMyCustomData();
        $from_date = $this->_datetime->gmtDate(null, strtotime($data['c_start_date'] . ' 00:00:00'));
        $to_date = $this->_datetime->gmtDate(null, strtotime($data['c_end_date'] . ' 23:59:59'));
        
        $coll = $this->_helper->getcollect();
        $coll->getSelect()->reset(\Zend_Db_Select::COLUMNS)
          ->columns(array('SUM(qty_ordered) as order_quantity', 'sku', 'product_id', 'name', 'price', '(SUM(qty_ordered) * price) as revenue'))
                ->where(sprintf('salesord.created_at > "%s" AND salesord.created_at < "%s"', date('Y-m-d H:i:s', strtotime($from_date)), date('Y-m-d H:i:s', strtotime($to_date))))
                ->group('sku');
        
        $tqty = $qty = 0;
        foreach($coll as $c){
            if($sku == $c->getData('sku')){
                $qty = $c->getData('order_quantity');
            }
            $tqty += $c->getData('order_quantity');
         }
         if($tqty == 0) {$tqty = 1;}
         return number_format(($qty*100)/$tqty,2); 
    }
    
    /**
     * 
     * @param \Magento\Framework\DataObject $row
     * @return type
     */
    public function renderExport(\Magento\Framework\DataObject $row){
        $qty = $row->getData('order_quantity');
        $this->total_qty = $this->getcurrentqty();
        $a = number_format(($qty*100)/$this->total_qty,2);      
        $b = $this->getpreviousqty($row->getData('sku'));
        if($a=='nan'){ $a = 0;}
        if($b=='nan'){ $b = 0;}
            return $a - $b;
        
    }

}