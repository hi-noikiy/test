<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Pickupby extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<input type="text" id="pickup_by'.$row->getPickupId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        return $html;
    }
}