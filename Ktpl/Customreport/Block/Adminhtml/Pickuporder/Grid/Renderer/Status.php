<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
        //$html = parent::render($row);
        //$url = Mage::helper("adminhtml")->getUrl("*/*/updateCpp");
        $rowval = $row->getData($this->getColumn()->getIndex());

 		$html = '<select id="status'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '" style="width:70px;">';
        $selval1 = ""; $selval2 = ""; $selval3 = ""; $selval4 = ""; $selval5 = "";
        if($rowval == 1) {
        	$selval1 = 'selected="selected"';
    	} elseif($rowval == 2) {
    		$selval2 = 'selected="selected"';
        } elseif($rowval == 3) {
            $selval3 = 'selected="selected"';
        } elseif($rowval == 4) {
            $selval4 = 'selected="selected"';
    	} 
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selval1.'>Pending</option>';
        $html .= '<option value="2" '.$selval2.'>Cancel</option>';
        $html .= '<option value="3" '.$selval3.'>Complete</option>';
        $html .= '<option value="4" '.$selval4.'>On Hold</option>';
        $html .= '</select>';

 
        return $html;
    }
}