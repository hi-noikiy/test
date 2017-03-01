<?php

namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;

class Attributes extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
         $html = parent::render($row);
		$html = '<input type="text" id="attributes'.$row->getPickupId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
 
        return $html;
    }
}