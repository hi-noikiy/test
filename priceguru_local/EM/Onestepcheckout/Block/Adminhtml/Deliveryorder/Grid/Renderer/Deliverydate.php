<?php

class EM_Onestepcheckout_Block_Adminhtml_Deliveryorder_Grid_Renderer_Deliverydate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateDeposit");
        $rawvalue = "";
        if($row->getData($this->getColumn()->getIndex()) != "") {
            $rawvalue = date('d-m-Y H:i:s', strtotime($row->getData($this->getColumn()->getIndex())));
        }
        $html = '<input type="text" id="deliverydate'.$row->getDeliveryId().'" name="'.$this->getColumn()->getId().'" value="'.$rawvalue.'" class="input-text delivery-datetime">';
        $html .= '<script type="text/javascript">
            Calendar.setup({
                inputField: "deliverydate'. $row->getDeliveryId() .'",
                ifFormat: "%e-%m-%Y %H:%M:%S",
                showsTime: true,
                button: "date_select_trig",
                align: "Bl",
                singleClick : true
            });
        </script>';
 
        return $html;
    }
}