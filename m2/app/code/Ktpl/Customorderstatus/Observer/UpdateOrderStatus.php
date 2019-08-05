<?php
namespace Ktpl\Customorderstatus\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateOrderStatus implements ObserverInterface
{
    protected $_helper;

    protected $_logger;

    public function __construct(\Ktpl\Customorderstatus\Helper\Data $_helper,\Psr\Log\LoggerInterface $logger){
        $this->_helper = $_helper;
        $this->_logger = $logger;

    }
	public function execute(\Magento\Framework\Event\Observer $observer) {

        $order = $observer->getOrder();
        $state = $order->getState();
        $status = $order->getStatus();
        $grandTotal = $order->getGrandTotal();
        $paymentCode = $order->getPayment()->getMethodInstance()->getCode();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer =  $objectManager->get('Magento\Customer\Model\Customer');
        //$customer->setWebsiteId('1');
        $customer->load($order->getCustomerId()); 

        //$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $customerGroupId = $order->getCustomerId() ? $customer->getGroupId() : 0; 
        $storeId = $order->getStoreId();

        $isEnable = $this->_helper->isModuleEnabled($storeId);
        $thresholdAmount = $this->_helper->getThresholdAmount($storeId);
        $validPaymentMethod = $this->_helper->getAvilablePaymentMethod($paymentCode, $storeId);
        $validCustomerGroup = $this->_helper->getCustomerGroup($customerGroupId, $storeId);
       
        if (($isEnable) && ($validPaymentMethod) && ($validCustomerGroup) && ($grandTotal >= $thresholdAmount))
        {
            $isCustomerNotified = FALSE;
            $comment = 'Order status from Processing to Payment Review (Manual).';
            $state = \Magento\Sales\Model\Order::STATE_HOLDED;
            $status = 'holded';
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusHistoryComment('',$status);
            $order->save();

            $state = \Magento\Sales\Model\Order::STATE_HOLDED;
            $status = 'paymentreview_manual';
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusHistoryComment($comment,$status);
            $order->save();
        }
    }
}