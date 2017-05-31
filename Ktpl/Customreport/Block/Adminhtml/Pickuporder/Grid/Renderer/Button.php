<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Button extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
         $url = $this->getUrl('*/*/updateRowFields', ['_current' => true, '_use_rewrite' => true]);
        $html = '<button onclick="updatePickupOrders(this, '. $row->getPickupId() .', \''. $url .'\'); return false">' . __('Update') . '</button>';
        return $html;
    }
}