<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class City extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
    public function render(\Magento\Framework\DataObject $row)
    {
        $billingAddress = '';
        
        $order_id = $row->getRealOrderId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        if($order->getBillingAddress()->getData()){
            $billingAddress = $order->getBillingAddress()->getCity();
        }    
       
        return $billingAddress;
       
    }
}