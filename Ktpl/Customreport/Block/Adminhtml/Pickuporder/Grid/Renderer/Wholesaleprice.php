<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;

class Wholesaleprice extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
        $url = $this->getUrl('*/*/updateMarkupField', ['_current' => true, '_use_rewrite' => true]);
 		$html = '<input type="text" id="wholesale_price'.$row->getPickupId().'" name="'.$this->getColumn()->getId().'" value="'.$row->getData($this->getColumn()->getIndex()).'" class="input-text ">';
        $html .= '<button onclick="updateMarkupField(this, '. $row->getPickupId() .', \''. $url .'\'); return false">' . __('Update') . '</button>';
        $html .= "<script type='text/javascript'>requirejs (['livegridedit'], function(){ });</script>"; 
        return $html;
    }
}