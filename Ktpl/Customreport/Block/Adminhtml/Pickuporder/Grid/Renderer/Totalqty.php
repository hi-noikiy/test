<?php

namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Totalqty extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
    public function render(\Magento\Framework\DataObject $row)
    {
       
        
        if($row->getTotal_qty_ordered()){
            $qty = (int) $row->getTotal_qty_ordered();
        }else{
            $qty = (int) $row->getQty();
        }
         
        
        return $qty;
    }
}