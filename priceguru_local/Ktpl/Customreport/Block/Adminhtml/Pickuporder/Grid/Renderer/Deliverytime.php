<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Deliverytime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = '<span id="delivery_time'.$row->getPickupId().'" class="markup-text">'.$row->getData($this->getColumn()->getIndex()).'</span>';
        //return $html;
        $rawvalue = "";
        if($row->getData($this->getColumn()->getIndex()) != "") {
            $rawvalue = date('d-m-Y', strtotime($row->getData($this->getColumn()->getIndex())));
        }
        $html = '<input type="text" id="delivery_time'.$row->getPickupId().'" name="'.$this->getColumn()->getId().'" value="'.$rawvalue.'" class="input-text delivery-datetime">';
        $html .= '<script type="text/javascript">
            Calendar.setup({
                inputField: "delivery_time'. $row->getPickupId() .'",
                ifFormat: "%e-%m-%Y",
                showsTime: true,
                button: "date_select_trig",
                align: "Bl",
                singleClick : true
            });
        </script>';
 
        return $html;
    }
    
    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }
}