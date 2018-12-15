<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Pickupstatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    var $status = [2 => 'No',
              1 => 'Yes',
              3 => 'Waiting for stock',];

    public function render(Varien_Object $row) {
        return $this->status[$row->getData($this->getColumn()->getIndex())];
    }

    public function renderExport(Varien_Object $row) {
        return $this->status[$row->getData($this->getColumn()->getIndex())];
    }

}
