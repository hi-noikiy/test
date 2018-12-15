<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Clientconnect extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    var $status = [1 => 'Yes', 2 => 'No', ];

    public function render(Varien_Object $row) {
        $rowval = $row->getData($this->getColumn()->getIndex());
        $html = '<select id="client_connected' . $row->getDeliveryId() . '" name="' . $this->getColumn()->getId() . '" style="width:70px;">';
        $html .= '<option value=""></option>';
        foreach ($this->status as $k => $v):
            $selected = ($k == $rowval) ? 'selected' : '';
            $html .= '<option value="' . $k . '" ' . $selected . ' >' . $v . '</option>';
        endforeach;
        $html .= '</select>';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
        return $this->status[$row->getData($this->getColumn()->getIndex())];
    }
}