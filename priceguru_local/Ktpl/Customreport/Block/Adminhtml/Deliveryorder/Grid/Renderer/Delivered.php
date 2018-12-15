<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Delivered extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    var $status = [1 => '1124FB16',
              2 => '5744AG18',
              3 => 'YANNICK CAR',
              4 => '731ZY09',
              5 => '8713DC15',
              6 => 'CAR RENTAL 1',
              7 => 'CAR RENTAL 2',];

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

        $html = '<select id="delivered_by' . $row->getDeliveryId() . '" name="' . $this->getColumn()->getId() . '" style="width:70px;">';


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
