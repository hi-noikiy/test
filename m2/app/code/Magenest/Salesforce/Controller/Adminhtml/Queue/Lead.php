<?php
namespace Magenest\Salesforce\Controller\Adminhtml\Queue;

use Magenest\Salesforce\Model\Queue;
use Magenest\Salesforce\Model\QueueFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config;

/**
 * Class Lead
 * @package Magenest\Salesforce\Controller\Adminhtml\Queue
 */
class Lead extends \Magento\Backend\App\Action
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_configInterface;

    /**
     * @var Config
     */
    protected $_config;

    /**
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * @var string
     */
    protected $type = Queue::TYPE_LEAD;

    /**
     * Customer constructor.
     * @param Context $context
     * @param CustomerFactory $customerFactory
     * @param Config $config
     * @param ScopeConfigInterface $configInterface
     * @param QueueFactory $queueFactory
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        Config $config,
        ScopeConfigInterface $configInterface,
        QueueFactory $queueFactory
    ) {
        $this->queueFactory = $queueFactory;
        $this->_config = $config;
        $this->_configInterface = $configInterface;
        $this->customerFactory = $customerFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            /** @var \Magento\Customer\Model\Customer $customerCollection */
            $customerCollection = $this->customerFactory->create()->getCollection();
            /** @var \Magenest\Salesforce\Model\Queue $queue */
            $queue = $this->queueFactory->create();
            try {
                $queue->deleteQueueByType($this->type);
                $customerCollectionArr = [];
                $lastItemId = $customerCollection->getLastItem()->getId();
                $maxRecord = 5000;
                $count = 0;
                foreach ($customerCollection as $customer) {
                    $count++;
                    $customerCollectionArr[] = $queue->enqueue($this->type, $customer->getId());
                    if ($count >= $maxRecord || $customer->getId() == $lastItemId) {
                        $queue->enqueueMultiRecords($customerCollectionArr);
                        $customerCollectionArr = [];
                        $count = 0;
                    }
                }
//            try {
//                $customers = $this->customerFactory->create()->getCollection();
//                /** @var \Magento\Customer\Model\Customer $customer */
//                foreach ($customers as $customer) {
//                    $queue = $this->queueFactory->create();
//                    if (!$queue->queueExisted($this->type, $customer->getId())) {
//                        $queue->enqueue($this->type, $customer->getId());
//                    }
//                }
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('All Leads have been added to queue.'),
                ]));
                return;
            } catch (\Exception $e) {
                $this->getResponse()->setBody(json_encode([
                    'error' => 0,
                    'message' => __('Something went wrong while adding record(s) to queue. Error: '.$e->getMessage()),
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
