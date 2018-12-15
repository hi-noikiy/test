<?php

class EM_Onestepcheckout_Block_Adminhtml_Purchaseorder_Grid_Renderer_Wholesaler extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        //$html = parent::render($row);
        $rowval = $row->getData($this->getColumn()->getIndex());

 		//$html = '<select id="wholesaler'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" onchange="updatePickupaddress(this, '. $row->getPickupId() .', \''. $url .'\'); return false" style="width:70px;">';
        //$html = '<select id="wholesaler'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" style="width:70px;">';
        $wholesalers = Mage::getSingleton('onestepcheckout/wholesaler')->load($rowval);
        return $wholesalers->getName();
    }
}