<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Queue;

use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Order
 * @package Magenest\Salesforce\Controller\Adminhtml\Sync
 */
class Order extends \Magento\Backend\App\Action
{
    /**
     * @var
     */
    protected $orderFactory;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var string
     */
    protected $type = Queue::TYPE_ORDER;

    /**
     * @var int
     */
    protected $orderToInvoiceFlag;

    /**
     * Order constructor.
     * @param Context $context
     * @param OrderFactory $orderFactory
     * @param ScopeConfigInterface $scopeConfigInterface
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        ScopeConfigInterface $scopeConfigInterface,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Customer\Model\Customer $customerCollection */
            $orderCollection = $this->orderFactory->create()->getCollection();
            /** @var \Magenest\Salesforce\Model\Queue $queue */
            $queue = $this->queueFactory->create();
            try {
                $queue->deleteQueueByType($this->type);
                $orderCollectionArr = [];
                $lastItemId = $orderCollection->getLastItem()->getIncrementId();
                $maxRecord = 5000;
                $count = 0;
                foreach ($orderCollection as $order) {
                    $count++;
                    $orderCollectionArr[] = $queue->enqueue($this->type, $order->getIncrementId());
                    if ($count >= $maxRecord || $order->getIncrementId() == $lastItemId) {
                        $queue->enqueueMultiRecords($orderCollectionArr);
                        $orderCollectionArr = [];
                        $count = 0;
                    }
                }
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('All Orders have been added to queue.')
                ]));
                return;
            } catch (\Exception $e) {
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('Something went wrong while adding record(s) to queue. Error: '.$e->getMessage())
                ]));
                return;
            }
        } else {
            return $this->_redirect('*/*/index');
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Salesforce::config_salesforce');
    }
}
