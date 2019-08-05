<?php
namespace Ktpl\Customorderstatus\Observer;

use Magento\Framework\Event\ObserverInterface;

class ChangeStatusPending implements ObserverInterface
{
	
	protected $_helper;

    protected $_logger;

    public function __construct(\Ktpl\Customorderstatus\Helper\Data $_helper,\Psr\Log\LoggerInterface $logger){
        $this->_helper = $_helper;
        $this->_logger = $logger;

    }
	public function execute(\Magento\Framework\Event\Observer $observer) {
		$order = $observer->getOrder();
        $status = $order->getStatus(); 
        $state = $order->getState();

        if ($status == 'paymentreview_manual' && $state == NULL || $state == '')
        {
            $isCustomerNotified = FALSE;
            $comment = 'Order status from Payment Review (Manual) to Pending.';
            $state = \Magento\Sales\Model\Order::STATE_NEW;
            $status = 'pending';
            $order->setState($state);
            $order->setStatus($status);
            $order->addStatusHistoryComment($comment,$status);
            $order->save();
        }
    }
}