<?php


class Ktpl_Salesreport_Block_Adminhtml_Salesdata_Renderer_Position extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected $total_qty;
    public function render(Varien_Object $row) {   
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
    public function getcurrentqty(){
        $coll =  Ktpl_Salesreport_Block_Adminhtml_Salesdata_Grid::getitems(); 
        $tqty = 0;
        foreach($coll as $c){
            $tqty += $c->getData('order_quantity');
         }
         return $tqty;
    }
    
    public function getpreviousqty($sku){
        
        $session = Mage::getSingleton('core/session');
        $data = $session->getMyCustomData();
        $from_date = Mage::getModel('core/date')->gmtDate(null, strtotime($data['c_start_date'] . ' 00:00:00'));
        $to_date = Mage::getModel('core/date')->gmtDate(null, strtotime($data['c_end_date'] . ' 23:59:59'));
        
        $coll = mage::helper('salesreport')->getcollect();
        $coll->getSelect()->reset(Zend_Db_Select::COLUMNS)
          ->columns(array('SUM(qty_ordered) as order_quantity', 'sku', 'product_id', 'name', 'price', 'SUM(qty_ordered) * price as revenue'))
                ->where(sprintf('salesord.created_at > "%s" AND salesord.created_at < "%s"', date('Y-m-d H:i:s', strtotime($from_date)), date('Y-m-d H:i:s', strtotime($to_date))))
                ->group('sku');
        
        $tqty = $qty = 0;
        foreach($coll as $c){
            if($sku == $c->getData('sku')){
                $qty = $c->getData('order_quantity');
            }
            $tqty += $c->getData('order_quantity');
         }
         return number_format(($qty*100)/$tqty,2); 
    }
    
    public function renderExport(Varien_Object $row){
        $qty = $row->getData('order_quantity');
        $this->total_qty = $this->getcurrentqty();
        $a = number_format(($qty*100)/$this->total_qty,2);      
        $b = $this->getpreviousqty($row->getData('sku'));
        if($a=='nan'){ $a = 0;}
        if($b=='nan'){ $b = 0;}
            return $a - $b;
        
    }

}