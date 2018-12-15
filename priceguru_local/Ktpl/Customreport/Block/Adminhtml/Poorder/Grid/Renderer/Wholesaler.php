<?php

class Ktpl_Customreport_Block_Adminhtml_Poorder_Grid_Renderer_Wholesaler extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $url = Mage::helper("adminhtml")->getUrl("*/*/updatePickupaddress");
        $rowval = $row->getData($this->getColumn()->getIndex());
 		$html = '<select id="wholesaler'.$row->getPoId().'" name="'.$this->getColumn()->getId() . '" onchange="updatePickupaddress(this, '. $row->getPoId() .', \''. $url .'\'); return false" style="width:70px;">';
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
    public function renderExport(Varien_Object $row) {
        $wholesalers = Mage::getSingleton('customreport/wholesaler')->getCollection();
    	foreach ($wholesalers as $wholesaler) {
    	 	if($wholesaler->getId() == $row->getData($this->getColumn()->getIndex())){
    	 		return $wholesaler->getName();
    	 	}
    	}
       // return $row->getData($this->getColumn()->getIndex());
    }
}