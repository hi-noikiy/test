<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Pickupaddress extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateInstallments");
        //$html = '<input type="text" id="address'.$row->getCimorderId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        $html = '<textarea id="pickupaddress' . $row->getPickupId() . '" class="input-text" name="' . $this->getColumn()->getId() . '" type="text">' . $row->getData($this->getColumn()->getIndex()) . '</textarea>';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return  $row->getData($this->getColumn()->getIndex());
    }

}
