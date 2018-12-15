<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircustomer_Grid_Renderer_Dispatch extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        
        $rawvalue = "";
        if($row->getData($this->getColumn()->getIndex()) != "") {
            $rawvalue = date('d-m-Y', strtotime($row->getData($this->getColumn()->getIndex())));
        }
        $html = '<input type="text" id="dispatch_date'.$row->getRepairCustomerId().'" name="'.$this->getColumn()->getId().'" value="'.$rawvalue.'" class="input-text delivery-datetime">';
        $html .= '<script type="text/javascript">
            Calendar.setup({
                inputField: "dispatch_date'. $row->getRepairCustomerId() .'",
                ifFormat: "%e-%m-%Y",
                showsTime: true,
                button: "date_select_trig",
                align: "Bl",
                singleClick : true
            });
        </script>';
 
        return $html;
    }
}