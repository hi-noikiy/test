<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Customercomment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateInstallments");
        //$html = '<input type="text" id="address'.$row->getCimorderId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        $html = '<textarea rows="3" id="customercomment' . $row->getDeliveryId() . '" class="input-text" name="' . $this->getColumn()->getId() . '" type="text">' . $row->getData($this->getColumn()->getIndex()) . '</textarea>';
        //$html .= '<button onclick="updateField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return $row->getData($this->getColumn()->getIndex());
    }

}
