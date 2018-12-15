<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Region extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    var $region = [1 => 1, 2 => 2, 3 => '3A', 7 => '3B', 4 => 4, 5 => 5, 6 => '6A', 8 => '6B'];

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

        $html = '<select id="region' . $row->getDeliveryId() . '" name="' . $this->getColumn()->getId() . '" style="width:50px;">';

        $html .= '<option value=""></option>';
        foreach ($this->region as $k => $v):
            $selected = ($k == $rowval) ? 'selected' : '';
            $html .= '<option value="' . $k . '" ' . $selected . ' >' . $v . '</option>';
        endforeach;
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return $this->region[$row->getData($this->getColumn()->getIndex())];
    }

}
