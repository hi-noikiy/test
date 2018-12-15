<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Commenthistory extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
    /*
     *  Get Invoice comment from order id
     */

    public function render(Varien_Object $row) {
        $html = '';
        $order_id = $row->getRealOrderId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $history = $order->getStatusHistoryCollection();
        //echo '<pre />'; print_r($history->getData()); exit;
        foreach ($history as $k => $com) {

            if ($com->getComment()) {
                if ($k != 0) {
                    $html .= "\n";
                }
                $html .= $com->getComment();
                $html .= "\n";
            }
        }
        $ht = '<textarea disabled="disabled" rows="3" cols="50" id="comment_history' . $row->getDeliveryId() . '" class="input-text" name="' . $this->getColumn()->getId() . '" type="text">' . $html . '</textarea>';
        return $ht;
    }

    public function renderExport(Varien_Object $row) {
        $html = '';
        $order_id = $row->getRealOrderId();
        $order = Mage::getModel('sales/order')->load($order_id);
        $history = $order->getStatusHistoryCollection();
        //echo '<pre />'; print_r($history->getData()); exit;
        foreach ($history as $k => $com) {

            if ($com->getComment()) {
                if ($k != 0) {
                    $html .= "\n";
                }
                $html .= $com->getComment();
                $html .= "\n";
            }
        }

        return $html;
    }

}
