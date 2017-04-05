<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;
class Address extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
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
            $bill = $order->getBillingAddress()->getStreet(); 
            $billingAddress = $bill[0].','.$order->getBillingAddress()->getPostcode();
        }    
       
        return $billingAddress;
       
    }
}