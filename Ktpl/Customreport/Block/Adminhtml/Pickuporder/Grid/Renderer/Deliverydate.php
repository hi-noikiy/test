<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Deliverydate extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
              
        $rawvalue = "";
        if($row->getData($this->getColumn()->getIndex()) != "") {
            $rawvalue = date('d-m-Y', strtotime($row->getData($this->getColumn()->getIndex())));
        }
        $html = '<input type="text" id="delivery_date'.$row->getPickupId().'" name="'.$this->getColumn()->getId().'" value="'.$rawvalue.'" class="input-text delivery-datetime" data-mage-init=\'{"calendar": {"showTime": false}}\'>';
        $html .= '<script type="text/javascript">
            require(["jquery","mage/calendar"],function($){ 
                $("#delivery_date'.$row->getPickupId().'").datepicker({
                    inputField: "delivery_date'. $row->getPickupId() .'",
                    ifFormat: "%e-%m-%Y",
                    showsTime: true,
                    button: "date_select_trig",
                    align: "Bl",
                    singleClick : true
                });
            });
        </script>';
 
        return $html;
    }
}