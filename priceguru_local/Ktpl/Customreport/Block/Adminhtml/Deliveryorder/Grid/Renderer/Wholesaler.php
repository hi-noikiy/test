<?php

class Ktpl_Customreport_Block_Adminhtml_Pickuporder_Grid_Renderer_Wholesaler extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    public function render(Varien_Object $row) {
        //$html = parent::render($row);
        $url = Mage::helper("adminhtml")->getUrl("*/*/updatePickupaddress");
        $rowval = $row->getData($this->getColumn()->getIndex());

        $html = '<select id="wholesaler' . $row->getPickupId() . '" name="' . $this->getColumn()->getId() . '" onchange="updatePickupaddress(this, ' . $row->getPickupId() . ', \'' . $url . '\'); return false" style="width:70px;">';
        //$html = '<select id="wholesaler'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" style="width:70px;">';
        $wholesalers = Mage::getSingleton('customreport/wholesaler')->getCollection();
        /* if($rowval == 1) {
          $selval1 = 'selected="selected"';
          } elseif($rowval == 2) {
          $selval2 = 'selected="selected"';
          } elseif($rowval == 3) {
          $selval3 = 'selected="selected"';
          } elseif($rowval == 4) {
          $selval4 = 'selected="selected"';
          } elseif($rowval == 5) {
          $selval5 = 'selected="selected"';
          } else {
          $selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = "";
          } */

        $html .= '<option value=""></option>';
        foreach ($wholesalers as $wholesaler) {
            $selval = "";
            if ($wholesaler->getId() == $rowval) {
                $selval = 'selected="selected"';
            }
            $html .= '<option value="' . $wholesaler->getId() . '" ' . $selval . '>' . $wholesaler->getName() . '</option>';
        }
        $html .= '</select>';

        //$html .= '<button onclick="updateSelectField(this, '. $row->getCimorderId() .', \''. $url .'\'); return false">' . Mage::helper('onestepcheckout')->__('Update') . '</button>';

        return $html;
    }

    public function renderExport(Varien_Object $row) {
       return Mage::getModel('customreport/wholesaler')->load($row->getData($this->getColumn()->getIndex()))->getName();
    }

}
