<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Pickupby extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        $url = Mage::helper("adminhtml")->getUrl("*/*/updateMarkupField");
        /*$html = '<input type="text" id="pickup_by' . $row->getPickupId() . '" name="' . $this->getColumn()->getId() . '" value="' . $row->getData($this->getColumn()->getIndex()) . '" class="input-text ">';*/

         $html = '<select id="pickup_by' . $row->getPickupId() . '" name="' . $this->getColumn()->getId() . '" class="">';

        $PickupbyArray = Mage::getSingleton('customreport/pickupby')->getOptionArray();
        $cnt = count($PickupbyArray);
           $html .= '<option value="" ></option>';        
        foreach($PickupbyArray as $k => $v){
            $selected = ($k ==$row->getData($this->getColumn()->getIndex()) )?"selected" :''; 
           $html .= '<option value="'.$k.'" '.$selected.' >'. $v."</option>";
        }
        $html .= "</select>";



        return $html;
    }

    public function renderExport(Varien_Object $row) {
        $PickupbyArray = Mage::getSingleton('customreport/pickupby')->getOptionArray();        
        return $PickupbyArray[$row->getData($this->getColumn()->getIndex())];
    }

}
