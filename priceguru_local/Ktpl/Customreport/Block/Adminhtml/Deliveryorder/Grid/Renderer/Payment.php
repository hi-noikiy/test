<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Payment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateDcp");
        $html = '<input type="text" id="payment_method' . $row->getDeliveryId() . '" name="' . $this->getColumn()->getId() . '" value="' . $row->getData($this->getColumn()->getIndex()) . '" class="input-text ">';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }

}