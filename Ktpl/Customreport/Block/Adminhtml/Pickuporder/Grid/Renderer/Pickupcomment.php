<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Pickupcomment extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<textarea id="pickup_comment'.$row->getPickupId().'" class="input-text" name="'.$this->getColumn()->getId().'" type="text">'.$row->getData($this->getColumn()->getIndex()).'</textarea>';
 
        return $html;
    }
}