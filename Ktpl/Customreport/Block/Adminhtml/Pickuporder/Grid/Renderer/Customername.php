<?php
namespace Ktpl\Customreport\Block\Adminhtml\Pickuporder\Grid\Renderer;

class Customername extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Input {
/*
 *  Get Invoice comment from order id
 */
   public function render(\Magento\Framework\DataObject $row)
    {
        $order_id = $row->getData('real_order_id');
        $customer_name = $row->getData('customer_name');

        if($customer_name) {
            $name = $customer_name;   
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Ktpl\Customreport\Model\Wholesaler')->load($order_id);
            //$order = Mage::getModel('sales/order')->load($order_id);
            $name = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        }
        
        return $name;
    }
}