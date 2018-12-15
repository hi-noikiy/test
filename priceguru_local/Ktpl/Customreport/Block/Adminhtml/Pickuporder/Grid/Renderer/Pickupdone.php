<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Pickupdone extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    var $status = [2 => 'No', 1 => 'Yes', 
        3 => 'Waiting for stock',
        ];

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

        $html = '<select id="pickup_done' . $row->getPickupId() . '" name="' . $this->getColumn()->getId() . '" style="width:70px;">';


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
