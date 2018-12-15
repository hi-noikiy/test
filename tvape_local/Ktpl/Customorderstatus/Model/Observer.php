<?php
class Ktpl_Customorderstatus_Model_Observer
{
	public function updateOrderStatus($observer)
    {
        $helper = Mage::helper('customorderstatus');

        $order = $observer->getOrder();
        $state = $order->getState();
        $status = $order->getStatus();
        $grandTotal = $order->getGrandTotal();
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $customerGroupId = $customer->getGroupId();
        $storeId = $order->getStoreId();

        $isEnable = $helper->isModuleEnabled($storeId);
        $thresholdAmount = $helper->getThresholdAmount($storeId);
        $validPaymentMethod = $helper->getAvilablePaymentMethod($paymentCode, $storeId);
        $validCustomerGroup = $helper->getCustomerGroup($customerGroupId, $storeId);

        if (($isEnable) && ($validPaymentMethod) && ($validCustomerGroup) && ($grandTotal >= $thresholdAmount))
        {
        	$isCustomerNotified = FALSE;
            $comment = 'Order status from Processing to Payment Review (Manual).';
            $state = Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW;
            $status = 'paymentreview_manual';
            $order->setState($state, $status, $comment, $isCustomerNotified);
            $order->save();
        }
    }
}