<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Clientconnect extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
       $selvalyes = "";
    		$selvalno = "";
        $rowval = $row->getData($this->getColumn()->getIndex()); 

 		$html = '<select id="client_connected'.$row->getPickupId().'" name="'.$this->getColumn()->getId() . '">';
        
        if($rowval == 1) {
        	$selvalyes = 'selected="selected"';
    	} elseif($rowval == 0) {
    		$selvalno = 'selected="selected"';
    	} 
    	$html .= '<option value=""></option>';
    	$html .= '<option value="1" '.$selvalyes.'>Yes</option>';
        $html .= '<option value="0" '.$selvalno.'>No</option>';
        $html .= '</select>';

 
        return $html;
    }
}