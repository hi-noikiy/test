<?php

class Ktpl_Repaircenter_Block_Adminhtml_Repaircenter_Grid_Renderer_Wholesaler extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row)
    {
         
        $rowval = $row->getData($this->getColumn()->getIndex());
	$html = '<select id="wholesaler'.$row->getRepairId().'" name="'.$this->getColumn()->getId().'>';
        $wholesalers = Mage::getSingleton('customreport/wholesaler')->getCollection();
    

    	$html .= '<option value=""></option>';
        foreach ($wholesalers as $wholesaler) {
            $selval = "";
            if($wholesaler->getId() == $rowval) { $selval = 'selected="selected"'; }
            $html .= '<option value="'. $wholesaler->getId() .'" '.$selval.'>'. $wholesaler->getName() .'</option>';
        }
        $html .= '</select>';
 
        return $html;
    }
}