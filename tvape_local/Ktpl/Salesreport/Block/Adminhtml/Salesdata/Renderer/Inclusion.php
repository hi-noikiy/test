<?php


class Ktpl_Salesreport_Block_Adminhtml_Salesdata_Renderer_Inclusion extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    protected $total_qty;
    public function render(Varien_Object $row) {   
        $qty = $row->getData('order_quantity');
        $this->total_qty = $this->getqty();
        $a = number_format(($qty*100)/$this->total_qty,2);      
       return $a.'%';
    }
    
    public function getqty(){
        $coll =  Ktpl_Salesreport_Block_Adminhtml_Salesdata_Grid::getitems(); 
        $tqty = 0;
        foreach($coll as $c){
            $tqty += $c->getData('order_quantity');
         }
         return $tqty;
    }

}