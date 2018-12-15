<?php

class Ktpl_Customreport_Block_Adminhtml_Deliveryorder_Grid_Renderer_Exportdelivery extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(Varien_Object $row)
    {
        $html = '';
        $order_id = $row->getRealOrderId();
        $order=Mage::getModel('sales/order')->load($order_id);
        $history = $order->getStatusHistoryCollection();
       //echo '<pre />'; print_r($history->getData()); exit;
        foreach($history as $k=>$com){
            if($com->getComment()){
                 if($k!=0){$html .= "\n";}
                $html .=  $com->getComment();
                $html .= "\n";
            }    
        }
       // $html .= $row->getData($this->getColumn()->getIndex());
      //  echo $html; exit;
        return $html;
    }
}