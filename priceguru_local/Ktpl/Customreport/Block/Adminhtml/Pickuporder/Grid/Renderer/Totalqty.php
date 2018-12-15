<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Totalqty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
       
        
        if($row->getTotal_qty_ordered()){
            $qty = (int) $row->getTotal_qty_ordered();
        }else{
            $qty = (int) $row->getQty();
        }
         
        
        return $qty;
    }
}