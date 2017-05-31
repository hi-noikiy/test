<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;

class Markup extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
        $val = $row->getData($this->getColumn()->getIndex());
        if($val != "" && $val > 0) {
        	$val = $val."%";
        }
 		$html = '<span id="markup'.$row->getPickupId().'" class="markup-text">'.$val.'</span>';
 
        return $html;
    }
}