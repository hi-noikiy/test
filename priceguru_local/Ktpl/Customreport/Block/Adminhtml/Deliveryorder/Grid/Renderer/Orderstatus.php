<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Orderstatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {

    public function render(Varien_Object $row) {
        $order_id = $row->getData('real_order_id');
        $ords = $row->getData('order_status');

       /* if($ords && trim($ords) !='') { 
            $name = $ords;
        } else { */
            $order = Mage::getModel('sales/order')->load($order_id);
            $name = $order->getStatus();
      //  }
        
        return $name;
    }

//    public function renderExport(Varien_Object $row) {
//    }

}
