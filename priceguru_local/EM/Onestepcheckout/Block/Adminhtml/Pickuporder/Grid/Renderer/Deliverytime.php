<?php

class EM_Onestepcheckout_Block_Adminhtml_Pickuporder_Grid_Renderer_Deliverytime extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateDeposit");
 		$html = '<span id="delivery_time'.$row->getPickupId().'" class="markup-text">'.$row->getData($this->getColumn()->getIndex()).'</span>';
        //$html .= '<button onclick="updateField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';
 
        return $html;
    }
}