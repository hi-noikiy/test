<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Status extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    var $status = [1 => 'Pending', 2 => 'Cancel', 3 => 'Complete', 4 => 'On Hold'];

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

        $html = '<select id="status' . $row->getDeliveryId() . '" name="' . $this->getColumn()->getId() . '" style="width:70px;">';


        $html .= '<option value=""></option>';
        foreach ($this->status as $k => $v):
            $selected = ($k == $rowval) ? 'selected' : '';
            $html .= '<option value="' . $k . '" ' . $selected . ' >' . $v . '</option>';
        endforeach;
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return $this->status[$row->getData($this->getColumn()->getIndex())];
    }

}
