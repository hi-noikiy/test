<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Markup extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateDeposit");
        $val = $row->getData($this->getColumn()->getIndex());
        if($val != "" && $val > 0) {
        	$val = $val."%";
        }
 		$html = '<span id="markup'.$row->getPickupId().'" class="markup-text">'.$val.'</span>';
 
        return $html;
    }
}